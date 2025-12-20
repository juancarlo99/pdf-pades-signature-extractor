# PHP CLI 8.3
FROM php:8.3-cli

# Dependências básicas para libs PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    ca-certificates \
    openssl \
    && rm -rf /var/lib/apt/lists/*

# Instala Composer (imagem oficial)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Diretório de trabalho
WORKDIR /app

# Mantém o container ativo para uso interativo
CMD ["bash"]
