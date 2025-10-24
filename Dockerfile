FROM php:8.3-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    unzip \
    curl \
    bash \
    icu-dev \
    linux-headers \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-install \
    intl \
    opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first for better layer caching
COPY composer.json composer.lock* ./

# Install dependencies (only if composer.lock exists)
RUN if [ -f composer.lock ]; then \
        composer install --no-scripts --no-autoloader --no-interaction; \
    fi

# Copy application code
COPY . .

# Generate autoloader
RUN composer dump-autoload --optimize

# Configure git to trust the /app directory
RUN git config --global --add safe.directory /app

# Set up git hooks if needed
RUN if [ -d .githooks ]; then \
        git config core.hooksPath .githooks && \
        find .githooks -type f -exec chmod +x {} \; || true; \
    fi

# Default command
CMD ["php", "--version"]
