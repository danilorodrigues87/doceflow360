# DoceFlow — cPanel

## Erro 403 HostGator em doceflow.xd360.com.br

**Causa mais comum:** Document Root do subdomínio aponta para a **raiz do clone** (sem `index.php` visível) ou permissões erradas.

**Correção recomendada (cPanel → Domains → doceflow.xd360.com.br):**

- Document Root = `.../doceflow.xd360.com.br/public`

Permissões: pastas **755**, arquivos **644**; dono = usuário cPanel.

**Alternativa:** Document Root na raiz do repo — o `.htaccess` na raiz (Git) redireciona tudo para `public/`. Faça deploy/pull e confira se `.htaccess` existe no servidor.

Teste: `https://doceflow.xd360.com.br/index.php/api/health`

## Composer (sem bin global)

```bash
cd ~/doceflow.xd360.com.br
curl -sS https://getcomposer.org/installer -o composer-setup.php
/usr/local/bin/ea-php81 composer-setup.php --install-dir=. --filename=composer.phar
rm -f composer-setup.php
/usr/local/bin/ea-php81 composer.phar install --no-dev --no-interaction
ls vendor/autoload.php
```

## `.env` produção

| DoceFlow | Painel XD360 |
|----------|----------------|
| `JWT_KEY` | **mesmo valor** |
| `PRODUCT_LAUNCH_SECRET` | **mesmo valor** (sem aspas, sem espaço no fim) |
| `XD360_API_URL=https://app.xd360.com.br` | `URL=https://app.xd360.com.br` |
| `DOCEFLOW_APP_HOST=doceflow.xd360.com.br` | `DOCEFLOW_PUBLIC_URL=https://doceflow.xd360.com.br` |

## "Token inválido ou expirado" ao Abrir app

1. **Secret diferente** entre os dois `.env` → corrija e tente **Abrir app** de novo (token novo).
2. **F5 na página do launch** → token é de uso único; clique Abrir app outra vez.
3. **DoceFlow não alcança a API:** `XD360_API_URL` errado ou cURL desabilitado (código novo usa cURL).
4. **Produto não no plano** → mensagem do painel: "Produto não licenciado neste plano."
5. Teste: `scripts/testar-launch-exchange.php?token=...` (remover depois).
