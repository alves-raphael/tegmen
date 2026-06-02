# State

## 2026-06-02 — Duplicate document: proper error + navigate back to step 1

- `UniqueDocument` custom rule: strips mask to digits, queries `customers.document`, accepts optional `$ignoreId` for edit flows
- `CustomerValidationRules` trait: added `UniqueDocument` to `document` rules in `step1Rules()` (ignores current customer on edit via `$this->customer->id ?? null`)
- Customer create `save()`: wrapped `validate()` in try/catch; if any step 1 field has an error, sets `$currentStep = 1` before re-throwing so Livewire renders the inline error in the right step
- 53 customer tests passing (2 new: duplicate triggers field error; save from step 2 navigates back to step 1)

## 2026-06-02 — Vehicle model_year supports double year format (e.g. 2020/2021)

- Migration widens `model_year` column from `VARCHAR(4)` to `VARCHAR(9)`
- `ValidModelYear` custom rule: validates each year part is 1900–(current year+1); rejects second year < first year
- `VehicleValidationRules` trait: replaced `digits:4/integer/min/max` with `regex:/^\d{4}(\/\d{4})?$/` + `ValidModelYear`
- Create/Edit Livewire views: `maxlength` updated to 9, placeholder updated, `maskModelYear` JS auto-inserts `/` after 4th digit
- `VehicleFactory`: added `doubleYear()` state generating `"YYYY/YYYY+1"` format
- 39 vehicle tests passing (9 new tests covering double year accept/reject, boundary years, edit pre-population)

## 2026-06-01 — Customer CPF → Document + Type (CPF & CNPJ support)

- Migration adds `document` (string 14, digits-only, unique), `type` (enum person/company) to customers; `cpf` and `birth_date` made nullable
- `CustomerType` enum (`Person`/`Company`) with `label()` helper
- `ValidCnpj` rule mirrors `ValidCpf` (14-digit algorithm, strips mask before checking)
- `customers:migrate-cpf-to-document` Artisan command: copies `cpf` digits to `document`, sets `type = 'person'`; idempotent via chunk
- `CustomerValidationRules` trait: dynamic `document` regex + rule based on `type`; `birth_date` nullable for companies
- Create/Edit Livewire pages: `cpf` property replaced with `document` + `type`; type radio toggles CPF/CNPJ label, placeholder, and JS mask; `birth_date` hidden for companies; digits stripped before DB write; edit `mount()` formats stored digits back for display
- `CustomerFactory`: default state uses `document` (CPF digits), `cpf = null`; new `company()` state with CNPJ digits
- `maskCnpj` JS helper added (`##.###.###/####-##` format)
- 166 tests passing (20 new/updated customer tests + 5 migration command tests)

## 2026-04-23 — CI artifact packaging snapshot fix

- Updated `tests.yml` artifact step to use `set -euo pipefail`
- Staged deploy artifact from a `mktemp` snapshot via `rsync` before `tar`
- Added `trap` cleanup for the temporary staging directory
- Excluded `.git`, `node_modules`, `.github`, `.env`, and transient `storage/framework` + `storage/logs` paths from artifact snapshot

## 2026-04-22 — CI/CD workflow hardening

- Added workflow `concurrency` per branch to avoid overlapping/stale runs
- Restricted `build-deploy` execution to pushes on `main`
- Removed CI xdebug coverage in test job to reduce runtime overhead
- Switched deploy artifact to unique name (`app-${{ github.run_id }}.tar.gz`) to avoid overwrite races
- Removed build-time Laravel cache generation from CI artifact
- Moved `optimize:clear`, `config:cache`, `route:cache`, and `view:cache` to server-side deploy after `.env` symlink

## 2026-04-22 — Reuse CI artifact for deployment

- `ci` now prepares and uploads a production artifact on `push` to `main`
- `build-deploy` now downloads that artifact instead of rebuilding dependencies/assets
- Removed duplicate `npm ci`/`npm run build` and production `composer install` from deploy job

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

## 2026-04-16 — Policy Edit (commission + notes only)

- Added `policies/{policy}/edit` route and `⚡edit.blade.php` Livewire SFC page
- Edit page reuses form via `_form.blade.php` partial (shared with create); `$readonly=true` disables all fields except `commission_percentage`, `commission_value`, `notes`
- `editRules()` added to `PolicyValidationRules` trait (commission + notes validation only)
- Edit component uses single-record computed properties (`customers`, `insurers`, `vehicles`) that return only the pre-selected records — no unnecessary data fetching
- `save()` on edit only writes the three editable fields; `policy_number` and other immutable fields are ignored even if tampered via devtools
- Edit button (`pencil-square`) added to policies listing
- 136 tests passing (10 new edit tests)

## 2026-04-16 — CRUD Policies

- Added `policies` migration: `customer_id`, `vehicle_id`, `insurer_id`, `policy_number`, `status` (enum, default ACTIVE), `start_date`, `end_date`, `premium`, `commission_percentage`, `commission_value`, `renewed_from_id` (self-ref FK, nullOnDelete), `notes`, `cancelled_at`; unique on `(insurer_id, policy_number)`
- `PolicyStatus` PHP 8.4 backed enum with `label()`, `badgeVariant()`, `isCancellable()`, `isRenewable()` helpers
- `Policy` model scoped via `customer.user_id` (no direct user_id column); `belongsToUser()` delegates to customer relationship
- `PolicyValidationRules` trait; `InsuranceCompanyFactory` + `PolicyFactory` with `active/expired/cancelled/renewed/expiringSoon` states
- `InsuranceCompany` model updated with `HasFactory`
- Livewire SFC pages: `policies/{index,create}`; no edit (spec-defined)
- Index: status filter (default=ACTIVE), sort by end_date ASC, status/days badges, cancel modal, renewal link; policy_number column added for identification
- Create: searchable combos for customer/insurer/vehicle; vehicle combo disabled until customer selected; date DD/MM/YYYY masks; currency mask (R$) for premium; commission auto-calculated on premium/percentage change; readonly commission_value; renewal pre-fill via `?renew_from=` mount param; atomic transaction marks origin as RENEWED
- Edge cases: renewal of non-ACTIVE blocked in mount + save; renewal mismatch logs warning + toast; cancellation state-machine guards; `unset($this->policies)` busts computed cache after cancel
- `window.maskCurrency` added to app.js; "Apólices" nav item added to sidebar
- Routes: `policies.{index,create}` in `routes/policies.php`
- 126 tests passing (84 existing + 42 new Policies tests)

## 2026-04-15 — Optional address in customer forms

- Address step (step 2) is now optional in both create and edit customer forms
- Added `hasAddressData()` helper: returns true if any core address field is non-empty; `save()` only applies `step2Rules()` and creates/updates address when true
- `updateAddress()` in edit form returns early when no address data, preserving any existing address
- Step 2 heading updated to "Endereço (opcional)" with explanatory subtext; `required` removed from all step 2 inputs
- 84 tests passing
