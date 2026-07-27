# Arquitetura

O projeto é uma aplicação Laravel monolítica modular. A camada HTTP e o painel Filament recebem requisições, serviços concentram regras de aplicação reutilizáveis, modelos representam persistência e os recursos Blade/Vite entregam a interface.

## Organização

- `app/Http`: controllers, requests e middleware.
- `app/Services`: casos de uso e integrações de aplicação.
- `app/Models`: entidades Eloquent e relações.
- `app/Filament`: recursos e páginas administrativas.
- `app/Observers`: observers Eloquent (ex.: `UserObserver` para banimento).
- `database`: migrations, factories e seeders.
- `resources`: Blade, JavaScript e CSS.
- `routes`: rotas web e de console.
- `docker/`: imagens, Nginx e inicialização do PostgreSQL.

## Separação Admin × Site

A tabela `users` é única, mas o campo `type` (`UserType::Admin` ou `UserType::SiteUser`) diferencia o papel do usuário. `User::canAccessPanel` exige `type === Admin` **e** permissão `admin.access`, garantindo que usuários do site não acessem `/admin` mesmo que estejam autenticados. Admins também podem comentar e enviar depoimentos quando autenticados no site.

O fluxo de comunidade depende dos seguintes serviços:

- `EngagementService` centraliza submissão, aprovação, rejeição e banimento (com snapshot de nome/e-mail/cidade).
- `ProfanityFilter` aplica a lista configurável de `config/profanity.php` (palavras em PT-BR).
- `PostRatingAggregator` calcula média, contagem e distribuição de notas por post a partir dos comentários aprovados.

Evite criar uma camada nova quando uma existente atende ao caso. Mudanças arquiteturais exigem autorização e documentação nesta página.
