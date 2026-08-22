-- Esquema del micro-monolito Micro-HIS QA / E2E / CI.
-- Se usa SQLite vía PDO para no requerir un servidor externo,
-- manteniendo sentencias preparadas y llaves foráneas activas.

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS test_plans (
    id                     TEXT PRIMARY KEY,
    release_version        TEXT NOT NULL,
    description            TEXT NOT NULL,
    quality_gate_threshold REAL NOT NULL,
    created_at             TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS test_executions (
    id             TEXT PRIMARY KEY,
    test_plan_id   TEXT NOT NULL,
    scenario_name  TEXT NOT NULL,
    environment    TEXT NOT NULL,
    status         TEXT NOT NULL,
    evidence_count INTEGER NOT NULL DEFAULT 0,
    finished_at    TEXT,
    FOREIGN KEY (test_plan_id) REFERENCES test_plans(id)
);

CREATE TABLE IF NOT EXISTS evidences (
    id           TEXT PRIMARY KEY,
    execution_id TEXT NOT NULL,
    type         TEXT NOT NULL,
    reference    TEXT NOT NULL,
    recorded_at  TEXT NOT NULL,
    FOREIGN KEY (execution_id) REFERENCES test_executions(id)
);
