# Keu-app Justfile
# Browser automation and development commands

APP_URL := "http://localhost:3000"
API_URL := "http://localhost:8080"
FE_DIR := "fe-fitur"
BE_DIR := "be-fitur"

# Default recipe - list all available commands
default:
    @just --list

# ─── Development ─────────────────────────────

# Start all services (frontend + backend)
dev:
    @echo "Starting Keu-app services..."
    @just dev:be &
    @just dev:fe

# Start frontend only
dev:fe:
    @echo "Starting frontend at {{APP_URL}}..."
    cd {{FE_DIR}} && npm run dev

# Start backend only
dev:be:
    @echo "Starting backend at {{API_URL}}..."
    cd {{BE_DIR}} && php -S 127.0.0.1:8080 -t public

# ─── Testing ─────────────────────────────

# Run all tests
test:
    @just test:unit
    @just test:e2e

# Run unit tests only
test:unit:
    @echo "Running unit tests..."
    cd {{FE_DIR}} && npm run lint

# Run E2E tests with bowser
test:e2e headed="false" story="all":
    @echo "Running E2E tests..."
    claude --dangerously-skip-permissions --model opus "Use @bowser-qa-agent to test: {{story}} headed={{headed}}"

# ─── Browser Automation ─────────────────────────────

# Open Keu-app in browser
browser:open url=APP_URL:
    @echo "Opening {{url}}..."
    PLAYWRIGHT_MCP_VIEWPORT_SIZE=1440x900 playwright-cli -s=keu-manual open {{url}} --persistent --headed

# Run specific QA story
browser:test story="all" headed="false":
    @echo "Running QA test: {{story}}"
    claude --dangerously-skip-permissions --model opus "Use @bowser-qa-agent: {{story}} headed={{headed}}"

# Run all QA stories (UI Review)
browser:qa headed="false" filter="":
    @echo "Running UI review..."
    claude --dangerously-skip-permissions --model opus "/ui-review {{headed}} {{filter}}"

# Close all browser sessions
browser:cleanup:
    @echo "Closing all browser sessions..."
    playwright-cli close-all

# ─── Code Quality ─────────────────────────────

# Run ESLint
lint:
    @echo "Running ESLint..."
    cd {{FE_DIR}} && npm run lint

# TypeScript check
typecheck:
    @echo "Running TypeScript check..."
    cd {{FE_DIR}} && npx tsc --noEmit

# Build frontend
build:
    @echo "Building frontend..."
    cd {{FE_DIR}} && npm run build

# ─── Database ─────────────────────────────

# Run migrations
migrate:
    @echo "Running migrations..."
    cd {{BE_DIR}} && php artisan migrate

# Seed database
db:seed:
    @echo "Seeding database..."
    cd {{BE_DIR}} && php artisan db:seed

# Reset database
db:reset:
    @echo "Resetting database..."
    cd {{BE_DIR}} && php artisan migrate:fresh --seed

# ─── Screenshots ─────────────────────────────

# Take screenshot of current state
screenshot name="screenshot":
    @echo "Taking screenshot..."
    playwright-cli -s=keu-manual screenshot --filename="screenshots/{{name}}.png"

# Open screenshots folder
screenshots:open:
    @echo "Opening screenshots folder..."
    @if [ -d "screenshots" ]; then explorer screenshots; fi

# ─── Help ─────────────────────────────

# Show help
help:
    @echo "Keu-app Commands:"
    @echo ""
    @echo "Development:"
    @echo "  just dev          - Start all services"
    @echo "  just dev:fe       - Start frontend only"
    @echo "  just dev:be       - Start backend only"
    @echo ""
    @echo "Testing:"
    @echo "  just test         - Run all tests"
    @echo "  just test:unit    - Run unit tests"
    @echo "  just test:e2e      - Run E2E tests"
    @echo ""
    @echo "Browser:"
    @echo "  just browser:open  - Open app in browser"
    @echo "  just browser:qa    - Run UI review"
    @echo "  just browser:test  - Run specific QA story"
    @echo ""
    @echo "Code Quality:"
    @echo "  just lint         - Run ESLint"
    @echo "  just typecheck    - TypeScript check"
    @echo "  just build        - Build frontend"
    @echo ""
    @echo "Database:"
    @echo "  just migrate      - Run migrations"
    @echo "  just db:seed      - Seed database"
    @echo "  just db:reset     - Reset database"
