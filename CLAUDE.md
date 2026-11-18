# Pepperjam — Claude Code guidance

@AGENTS.md

## Project at a glance

- Pepperjam is a server-rendered Symfony application. Use the existing Twig pages and Symfony Forms; there is no API Platform/API-first architecture here.
- Runtime: PHP 8.4 in Docker (the Composer requirement is PHP >= 8.2), Symfony 7.4, Doctrine ORM 3, MariaDB 11, Twig, Symfony Security, Symfony UX Turbo, Stimulus, and Webpack Encore.
- The established stack is already selected. Do not ask whether to add an ORM, choose a UI architecture, or select an authentication system; follow the current implementation.
- The public site is English-facing. Keep labels, buttons, validation copy, and user-visible text in English unless the surrounding page is intentionally in another language.

## Run and inspect the app

Use Docker Compose from the repository root:

```bash
docker compose up -d --build
docker compose ps
```

- Site: `http://localhost:8080`
- Mailpit inbox: `http://localhost:8025` (SMTP service name inside Compose: `mailpit:1025`)
- MariaDB inside Compose: host `database`, port `3306`
- MariaDB from PhpStorm on the host: host `127.0.0.1`, port `3307`. Use the database/user credentials configured for the local Compose environment.
- Symfony console: `docker compose exec php php bin/console <command>`
- PHP dependencies and Symfony commands run in the `php` service. Frontend dependencies and Encore run in the `node` service.
- Build development assets when needed: `docker compose run --rm node npm run dev`. Production assets: `docker compose run --rm node npm run build`.
- Inspect service state/logs with `docker compose ps` and `docker compose logs --tail=100 <service>`.
- Inside Docker, `DATABASE_URL` must use `database:3306`; host tools such as PhpStorm use the published host port `3307`.

Do not assume every container is running. Check `docker compose ps` before diagnosing an unavailable site, database, or mail inbox. Do not reset/remove the `database_data` volume to fix routine connection issues.

## Application structure

- `src/Controller/`: HTTP routes. Main routes include home/contact, login/register, admin user approval, activity display, and project create/edit/delete.
- `src/Entity/` and `src/Repository/`: Doctrine entities and repositories. Important content entities are `Section`, `Activity`, `Project`, `Member`, `Software`, `Resound`, and `Media`; the user entity is named `Users`.
- `src/Form/`: Symfony forms, including section forms under `src/Form/Section/`.
- `src/Handler/Section/`: handlers for editing each homepage section.
- `src/Factory/SectionHandlerFactory.php`: maps section type names to their handlers.
- `src/Service/MediaService.php`: stores uploaded files under the configured upload directory and handles media replacement/removal. Reuse it rather than duplicating file-system logic.
- `src/Twig/SectionExtension.php`: Twig helpers for retrieving homepage sections and content collections.
- `templates/home/section/`: homepage sections, their edit forms, and Turbo replacement fragments. `templates/home/activity/` contains the activity/project pages.
- `assets/`: JavaScript entry and Stimulus/Turbo-related code. `public/assets/css/`: section stylesheets. Webpack Encore compiles `assets/app.js` to `public/build/`.

## Existing behavior to preserve

### Homepage editing and Turbo

- Homepage editing is dispatched through `/edit/section/{sectionType}` in `EditionController`, then through `SectionHandlerFactory` to a `SectionHandlerInterface` implementation.
- Each section form and response uses Turbo Streams to replace a specific DOM target. Keep the stream target ID aligned with the corresponding section ID and the template under `templates/home/section/turbo-load/`.
- When returning from an edit form—including Cancel—return the relevant Turbo replacement fragment so the section list/view updates without a full-page navigation. Follow the existing `_cancel` submit-button convention and handle it before normal form validation/persistence.
- Forms opened for an existing item pass its ID as a query parameter. Read it from the request query and return a not-found response for an unknown ID; do not silently create a new entity when an edit ID is invalid.
- Preserve existing media when an optional replacement upload is empty. Require an upload only when creating content that has no existing media. Use `MediaService` to replace files.
- Turbo-replaced fragments can be reinserted without a full page load. Bind JavaScript idempotently and account for Turbo navigation/replacement events where needed.

### Sections and content

- The homepage has distinct Showreel, Head Logo, Techreel, Collective, Team, Resound, Software, and Activities sections. Keep their media and settings attached to the existing section/entity model.
- Head Logo appearance settings (`logoAutoHide` and `logoDisplayDuration`) belong to the Head Logo section form/data, not the Showreel video form.
- Activities contain Projects. Use the activity/project routes and existing forms/services rather than inventing another content flow.
- Team, Resound, and Software have horizontally moving carousels. Preserve drag/touch interaction, existing arrow controls, pause/resume behavior, duplicate-track accessibility, and current edit-mode table/list displays when changing their markup or scripts.

### Security and configuration

- Admin editing is protected with `ROLE_ADMIN`; hiding edit controls in Twig is not authorization. Keep server-side access checks on every write/delete route.
- New accounts require the configured invitation code and are created disabled until an admin enables them. Do not bypass this approval flow.
- Login redirects to the home route. Avoid changing authentication/approval behavior as a side effect of unrelated work.
- Keep credentials and deployment secrets in environment configuration. Never copy `.env.dev`, `.env.local`, production environment values, or secret values into this file or into logs/comments.
- Production uses `compose.prod.yaml`; do not assume development Compose settings or local credentials apply in production.

## Change workflow

1. Inspect the relevant controller, form, handler/service, Twig fragment, and stylesheet/script before editing. Follow the existing feature path instead of introducing parallel abstractions.
2. Make the smallest complete change and preserve unrelated user modifications. Do not reset, clean, or overwrite files outside the requested scope.
3. For schema changes, add a Doctrine migration; never use `doctrine:schema:update` as a deployment workflow.
4. Validate the affected layer with available commands, for example:
   - `docker compose exec php php bin/console lint:container`
   - `docker compose exec php php bin/console lint:twig templates/`
   - `docker compose exec php php bin/console lint:yaml config/`
   - `docker compose run --rm node npm run dev` or `npm run build` for frontend changes.
5. There is currently no project test suite configured. Do not install or scaffold a test framework unless the user asks for tests; do run relevant syntax/configuration checks and report what was and was not verified.
6. Do not deploy, publish, migrate a production database, or send messages to external recipients unless the user explicitly requests that action.
