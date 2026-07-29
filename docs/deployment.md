# Deploy

O deploy recomendado utiliza Docker Compose e imagens versionadas. Configure `.env` a partir de `.env.example`, substitua todos os segredos e valide healthchecks antes de expor o Nginx.

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan optimize
```

Como o ambiente de deploy inicia com o banco vazio, a execução de `db:seed --force` é obrigatória após as migrations. O `DatabaseSeeder` cria permissões, papéis, usuários administrativos e o conteúdo inicial necessário para o funcionamento do painel e do site.

Execute o seeder completo apenas em bancos novos ou zerados. Em bancos com dados reais, avalie e execute um seeder específico para evitar alterações no conteúdo editorial. Após o primeiro acesso, substitua imediatamente as credenciais administrativas padrão definidas pelos seeders.

Faça backup do PostgreSQL e do bucket MinIO antes de migrations destrutivas. Não execute comandos de produção a partir de um agente sem autorização explícita.
