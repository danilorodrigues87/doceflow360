# DoceFlow — arquitetura

- **Tenant SaaS:** `tenant_id` = `xd360.clientes_assinantes.id`
- **Auth:** painel XD360 emite token one-time → `POST /api/v1/auth/exchange` → JWT (`JWT_KEY` compartilhada)
- **Dados:** banco MySQL `doceflow`, coluna `tenant_id` em todas as tabelas
- **Hospedagem MVP (Opção A):** path `/doceflow/` no host do cliente; UI Tailwind separada do painel Bootstrap
- **Fora do escopo agora:** domínio customizado do cliente (retomar só se o produto exigir)

Fluxo: ver diagrama em `xd360/ARCHITECTURE_XD360.md` (seção produtos com backend).

- **Persistência:** CRUD REST (diff no front após cada edição); sem backup/import JSON.
- **Tenant contratado:** começa vazio no MySQL; cadastro manual no app (sem import JSON / sem HTML offline).
- **Demo comercial:** **acesso teste** no XD360 (tenant demo ou conta de teste) + **Abrir app** — não distribuir HTML ao cliente final.
- **Login direto (roadmap):** hoje o app exige `?launch=` gerado após sessão no painel. Para login e-mail/senha só no DoceFlow: (1) tela `login` no front quando não há JWT; (2) `POST /api/v1/auth/login` no DoceFlow; (3) XD360 valida credenciais (mesmas regras do `/login`), checa módulo `doceflow` e assinatura ativa; (4) DoceFlow emite o mesmo JWT (`tenant_id` = `usuarios.id_admin`). Em subdomínio tenant, validar que o usuário pertence ao host; em path compartilhado (Opção A local), o tenant vem do usuário no token. Opcional: refresh token ou “lembrar sessão” além das ~8h atuais.
- **Blueprint outros produtos:** `PRODUCT_BACKEND_BLUEPRINT.md`.
