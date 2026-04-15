# State

## 2026-04-15 — CRUD Customer, Insurance Company Seeder, CRUD Vehicles

- Added `customers`, `addresses`, `insurance_companies`, `vehicles` migrations and models
- `Customer` scoped to `user_id`; `Address` supports versioning via `status` boolean; `Vehicle` has hardcoded `usage` enum and free-text `color`
- `InsuranceCompanySeeder` seeds 22 major Brazilian companies (idempotent via `firstOrCreate`)
- 6 Livewire SFC pages: `customers/{index,create,edit}` and `vehicles/{index,create,edit}`
- Multi-step customer form (2 steps: customer data → address) with Alpine.js masks for CPF, phone, CEP, date, license plate
- Address versioning: every address change deactivates old record and inserts new active one
- Authorization: `belongsToUser()` check in `mount()` + `save()`; unauthorized attempts logged with context
- Sidebar updated: "Plataforma" group with Clientes nav item
- `CustomerValidationRules`, `VehicleValidationRules` traits; `ValidCpf` custom rule
- Routes: `customers.{index,create,edit}` and `vehicles.{index,create,edit}` in `routes/customers.php`
- 82 tests passing (customers CRUD, vehicles CRUD, address versioning, seeder)
