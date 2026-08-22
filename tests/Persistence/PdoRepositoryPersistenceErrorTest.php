<?php

declare(strict_types=1);

namespace MicroHis\Tests\Persistence;

use MicroHis\Domain\Entity\TestExecution;
use MicroHis\Domain\Entity\TestPlan;
use MicroHis\Domain\Exception\PersistenceException;
use MicroHis\Domain\ValueObject\Environment;
use MicroHis\Persistence\Pdo\PdoConnectionFactory;
use MicroHis\Persistence\Pdo\PdoTestExecutionRepository;
use MicroHis\Persistence\Pdo\PdoTestPlanRepository;
use MicroHis\Tests\Support\TestCase;

/**
 * ERROR DE PERSISTENCIA: se usa una base de datos SQLite real de prueba
 * (en memoria, creada a partir del mismo schema.sql que usa producción)
 * para verificar que una violación de integridad referencial (llave
 * foránea) sea envuelta como PersistenceException y no se propague como
 * PDOException cruda hacia capas superiores.
 */
final class PdoRepositoryPersistenceErrorTest extends TestCase
{
    public function testGuardarEjecucionConPlanInexistenteLanzaErrorDePersistencia(): void
    {
        $pdo = PdoConnectionFactory::createInMemory();
        $repository = new PdoTestExecutionRepository($pdo);

        // No se crea el TestPlan 'PLAN-FANTASMA' a propósito: la FK debe fallar.
        $execution = new TestExecution(
            'EXEC-ERROR-1',
            'PLAN-FANTASMA',
            'Escenario sin plan asociado',
            new Environment('QA')
        );

        $this->assertThrows(
            PersistenceException::class,
            static fn () => $repository->save($execution),
            'Debe envolver la violación de llave foránea como PersistenceException.'
        );
    }

    public function testGuardarYRecuperarPlanFuncionaConBaseDePruebaReal(): void
    {
        $pdo = PdoConnectionFactory::createInMemory();
        $planRepository = new PdoTestPlanRepository($pdo);

        $plan = new TestPlan('PLAN-REAL-1', '3.0.0', 'Prueba con base real', 0.85);
        $planRepository->save($plan);

        $recovered = $planRepository->findById('PLAN-REAL-1');

        $this->assertTrue($recovered !== null, 'El plan debe recuperarse desde la base de prueba.');
        $this->assertEquals('3.0.0', $recovered->releaseVersion());
    }

    public function testGuardarEjecucionValidaSiFunciona(): void
    {
        $pdo = PdoConnectionFactory::createInMemory();
        $planRepository = new PdoTestPlanRepository($pdo);
        $executionRepository = new PdoTestExecutionRepository($pdo);

        $plan = new TestPlan('PLAN-REAL-2', '3.1.0', 'Plan válido', 0.85);
        $planRepository->save($plan);

        $execution = new TestExecution('EXEC-VALIDO-1', 'PLAN-REAL-2', 'Escenario válido', new Environment('QA'));
        $execution->start();

        $executionRepository->save($execution);
        $recovered = $executionRepository->findById('EXEC-VALIDO-1');

        $this->assertTrue($recovered !== null);
        $this->assertEquals('RUNNING', $recovered->status()->value);
    }
}
