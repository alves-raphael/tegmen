# Tegmen

> Sistema SaaS de gestão operacional para corretores autônomos de seguros de automóveis.

Tegmen atua como o **sistema de registro operacional do corretor**, centralizando o ciclo de vida das apólices, o cadastro de clientes e veículos, e o controle de comissões — enquanto a seguradora permanece a fonte legal de verdade do contrato.

---

## Funcionalidades

- **Clientes** — Cadastro completo com CPF, telefone e endereço versionado (histórico de endereços preservado a cada atualização).
- **Veículos** — Cadastro de veículos vinculados a clientes, com marca, modelo, ano, placa, cor e tipo de uso.
- **Apólices** — Gestão do ciclo de vida completo:
  - Estados: `ATIVA`, `RENOVADA`, `EXPIRADA`, `CANCELADA`
  - Renovação automática com pré-preenchimento do formulário
  - Cancelamento com confirmação via modal
  - Cálculo automático de comissão com base no prêmio e percentual
  - Filtro por status e ordenação por data de vencimento
  - Badges de prazo (verde / amarelo / vermelho) por dias restantes
- **Seguradoras** — Seed com 22 seguradoras brasileiras pré-cadastradas

---

## Stack

| Camada | Tecnologia |
|---|---|
| Linguagem | PHP 8.4 |
| Framework | Laravel 13 |
| Frontend | Livewire 4 + Flux UI 2 + Tailwind CSS v4 |
| Banco de dados | MySQL 8.4 |
| Cache / Filas | Redis |
| Storage | MinIO (S3-compatible) |
| Testes | Pest 4 |
| Infraestrutura local | Laravel Sail (Docker) |

---

## Requisitos

- Docker e Docker Compose
- Node.js 20+

---

## Instalação

```bash
# 1. Clone o repositório
git clone <repo-url> tegmen
cd tegmen

# 2. Copie o arquivo de ambiente
cp .env.example .env

# 3. Instale as dependências PHP
docker run --rm -v $(pwd):/app composer install

# 4. Suba os containers
./vendor/bin/sail up -d

# 5. Gere a chave da aplicação e execute as migrations
sail artisan key:generate
sail artisan migrate --seed

# 6. Instale as dependências JS e compile os assets
npm install && npm run build
```

Acesse em: [http://localhost](http://localhost)

---

## Desenvolvimento

```bash
# Sobe todos os serviços em modo dev (servidor, queue, logs, vite)
composer run dev
```

---

## Testes

```bash
# Executa todos os testes
sail artisan test --compact

# Filtra por nome
sail artisan test --compact --filter=PolicyTest
```

---

## Lint

```bash
# Corrige o estilo de código automaticamente
vendor/bin/pint --dirty
```

---

## Estrutura de Rotas

| Grupo | Prefixo | Arquivo |
|---|---|---|
| Clientes | `/customers` | `routes/customers.php` |
| Apólices | `/policies` | `routes/policies.php` |
| Configurações | `/settings` | `routes/settings.php` |

---

## Convenções

- Todos os identificadores de sistema (variáveis, tabelas, classes, métodos) são em **inglês**.
- A interface do usuário é em **português (pt-BR)** via Laravel Localization.
- UI mobile-first com Tailwind CSS e Flux UI.
- Cada alteração de endereço do cliente gera um novo registro, preservando o histórico.
- O escopo de dados é por usuário autenticado (`user_id`); nenhum dado de um corretor é acessível por outro.
