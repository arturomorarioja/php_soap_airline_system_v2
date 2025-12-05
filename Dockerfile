FROM php:8.2-cli

# Install SOAP extension (DOM is already available in the base image)
RUN apt-get update \
    && apt-get install -y libxml2-dev \
    && docker-php-ext-install soap \
    && rm -rf /var/lib/apt/lists/*

# Application code
WORKDIR /app
COPY . /app

# Default entrypoint: PHP CLI
ENTRYPOINT ["php"]
CMD ["-v"]