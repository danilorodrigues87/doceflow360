# DoceFlow — deploy (GitHub + servidor)

Repositório: `https://github.com/danilorodrigues87/doceflow360.git`

## Git (PowerShell, primeira vez)

```powershell
cd C:\xampp\htdocs\pjt\doceflow
git init
git remote add origin https://github.com/danilorodrigues87/doceflow360.git
git add .
git status
git commit --trailer "Co-authored-by: Cursor <cursoragent@cursor.com>" -m "DoceFlow: backend + app licenciado"
git branch -M main
git push -u origin main
```

Não commitar `.env` nem `vendor/` (use `composer install` no servidor).

## Servidor — vhost

| Host | DocumentRoot |
|------|----------------|
| `doceflow.xd360.com.br` | `.../doceflow/public` |

HTTPS. `mod_rewrite` ativo.

```bash
git clone https://github.com/danilorodrigues87/doceflow360.git /var/www/doceflow
cd /var/www/doceflow
composer install --no-dev --optimize-autoloader
cp .env.example .env
```

## MySQL

```bash
mysql -u USER -p < database/doceflow_init.sql
```

## `.env` produção

JWT_KEY e PRODUCT_LAUNCH_SECRET **iguais ao painel**.
XD360_API_URL=https://app.xd360.com.br
DOCEFLOW_APP_HOST=doceflow.xd360.com.br
XD360_MASTER_HOST=app.xd360.com.br
XD360_BASE_DOMAIN= (vazio)
DOCEFLOW_DEV_TENANT_SLUG= (vazio)

## Testes

1. https://doceflow.xd360.com.br/index.php/api/health
2. Painel → Abrir app DoceFlow
