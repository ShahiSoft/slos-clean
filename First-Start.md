# First-Start Guide: Setting Up Shahi LegalFlowSuite Development Environment

## Prerequisites

- PHP 7.4+
- Node.js 14+ (preferably 18+)
- Composer
- VS Code with extensions: PHP Intelephense, ESLint, Stylelint, Prettier

## Initial Setup

1. Clone or copy the project to your workspace.
2. Run `composer install` to install PHP dependencies.
3. Run `npm install` to install Node.js dependencies.
4. Run `npx husky install` to set up git hooks.

## VS Code Configuration

- Open the project in VS Code.
- The `.vscode/settings.json` is pre-configured for:
  - PHPCS with custom WordPress rules.
  - ESLint for JS.
  - Stylelint for CSS.
  - Prettier for formatting.
  - Auto-fix on save enabled.

## Development Workflow

- Write code following `CODING_GUIDELINES.md`.
- Run `npm run lint` to check JS/CSS.
- Run `./vendor/bin/phpcs` to check PHP.
- Use `npm run format` to format files.
- Commit changes; pre-commit hooks will run linters automatically.

## Testing

- Run `composer test` for PHPUnit tests.
- Ensure all files pass linters before submission.

## Codecanyon Submission

- Verify GPL compliance.
- Remove any demo data or external links.
- Package only required files using the provided scripts.
