# DoceFlow Pro (backend XD360)

API PHP multi-tenant + shell do app licenciado. Plataforma: [xd360](../xd360).

## Setup XAMPP

1. MySQL ligado.
2. Criar schema: `C:\xampp\mysql\bin\mysql.exe -u root < database/doceflow_init.sql`
3. No xd360: `mysql -u root xd360 < ../xd360/database/xd360_produto_launch.sql`
4. `composer install` (na pasta doceflow).
5. Copie `.env.example` → `.env` (ou use o `.env` já gerado) — **`JWT_KEY` e `PRODUCT_LAUNCH_SECRET` iguais ao xd360**.
6. No xd360 `.env`: `DOCEFLOW_PUBLIC_URL=` (vazio = `/doceflow/` no mesmo host do painel)

## URLs locais (Opção A)

| URL | Uso |
|-----|-----|
| `http://localhost/pjt/xd360/doceflow/` | App (após **Abrir app** no painel) |
| `http://localhost/pjt/xd360/painel/produtos` | Launch |
| `http://localhost/pjt/xd360/doceflow/index.php/api/health` | Health (via ponte) |

Detalhes: `docs/HOSTING.md`.

## Dados do cliente

- Contratou: tenant **zerado** no MySQL (sem import automático do navegador).
- Salvar: CRUD na nuvem (sem backup JSON).
- **Demonstração comercial:** não enviar HTML standalone ao cliente; use **acesso teste** (tenant demo ou assinante de teste) e **Abrir app** no painel XD360.
- Limpar demo antiga no banco: `DELETE FROM df_* WHERE tenant_id = ?` (phpMyAdmin).

## Login

- **Hoje:** entrada só via painel XD360 → **Abrir app** (token one-time → JWT ~8h).
- **Login direto no DoceFlow** (e-mail/senha na URL do app, sem abrir o painel): possível com nova API no XD360 + tela de login no app; ainda não implementado. Ver `docs/ARCHITECTURE.md`.

Padrão para novos produtos: `docs/PRODUCT_BACKEND_BLUEPRINT.md`.
