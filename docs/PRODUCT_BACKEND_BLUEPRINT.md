# Blueprint — produtos licenciados XD360

Padrão para DoceFlow, FitPro, etc.

## Princípios

1. **MySQL por produto** (`doceflow`, …) com `tenant_id` = `clientes_assinantes.id`.
2. **Auth:** painel → launch token → JWT.
3. **Opção A:** `{URL painel}/{produto}/` via ponte em `xd360/{produto}/index.php`.
4. **Persistência:** REST CRUD; sem JSON como motor de sync.
5. **Cliente novo:** dados zerados; cadastro no app.
6. **Demo antes de assinar:** **acesso teste** XD360 (tenant demo) — não entregar HTML offline ao prospect.

## Rotas típicas

- `POST /api/v1/auth/exchange`
- CRUD `/api/v1/{recurso}`
- `PATCH /api/v1/settings`
- `POST /api/v1/auth/login` (opcional — login direto no app sem abrir painel; validação no XD360)

## Evitar

- Import automático do localStorage.
- Dados fictícios no tenant pago sem opt-in.
