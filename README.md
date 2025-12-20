# PDF PAdES Signature Extractor

[EN](#english) | [PT-BR](#português-brasil)

---

## English

### 📄 Overview

**PDF PAdES Signature Extractor** is a PHP library designed to **extract technical data from digital signatures (PAdES)** embedded in signed PDF files.

This project was created as a **real-world, production-oriented library**, focused strictly on **data extraction**, not on legal or juridical validation.

> ⚠️ This library **does NOT validate the legal authenticity** of a digital signature.  
> It only extracts technical information from the embedded X.509 certificate.

---

### ✨ Features

- Detects PAdES signatures in PDF files  
- Extracts PKCS#7 signature blocks  
- Parses X.509 certificates  
- Extracts:
  - Signer name
  - CPF or CNPJ (ICP-Brasil)
  - Certificate issuer
  - Certificate validity period
  - Basic signature metadata
- Built on top of OpenSSL
- Clean architecture (DTOs, Services, Exceptions)
- PSR-4 compliant

---

### ❌ What this library does NOT do

- Certificate chain validation (ICP-Brasil)
- OCSP / CRL verification
- Timestamp Authority (TSA) validation
- Legal or juridical validation
- Signature trust evaluation

---

### 🧱 Requirements

- PHP **8.1+**
- OpenSSL extension enabled
- PDF signed using the **PAdES** standard

---

### 📦 Installation

```bash
composer require juancarlo99/pdf-pades-signature-extractor
```

---

### 🚀 Basic Usage

```php
use Juancarlo99\PdfPadesSignatureExtractor\Extractor\PadesExtractor;

$extractor = new PadesExtractor();
$signature = $extractor->extract('signed.pdf');

echo $signature->signerName;
echo $signature->cpf;
echo $signature->issuer;
```

---

### 📄 License

MIT License


### 👩‍💻 Development

Using Docker (recommended):

```bash
docker compose up -d
docker compose exec app composer install
docker compose exec app vendor/bin/phpunit --no-coverage
docker compose exec app vendor/bin/phpcs --standard=phpcs.xml src tests
docker compose exec app vendor/bin/phpstan analyse -c phpstan.neon
```

Composer scripts:

```bash
composer test
composer lint
composer stan
```

#### Sensitive tests

Some tests depend on local sample files and are annotated with the PHPUnit group `sensitive`. These tests are excluded in GitHub CI.

- Run all tests locally: `vendor/bin/phpunit`
- Run only sensitive tests locally: `vendor/bin/phpunit --group sensitive`
- CI excludes them via `--exclude-group sensitive` in the workflow.

---

## Português (Brasil)

### 📄 Visão Geral

**PDF PAdES Signature Extractor** é uma biblioteca PHP criada para **extrair dados técnicos de assinaturas digitais PAdES** presentes em arquivos PDF assinados.

Este projeto foi desenvolvido como uma **biblioteca real e prática**, com foco exclusivo em **extração de informações**, e não em validação jurídica.

> ⚠️ Esta biblioteca **NÃO valida juridicamente** a assinatura digital.  
> Ela apenas extrai dados técnicos do certificado X.509 embutido no PDF.

---

### ✨ Funcionalidades

- Detecta assinaturas PAdES em arquivos PDF  
- Extrai blocos PKCS#7  
- Lê certificados X.509  
- Extrai:
  - Nome do assinante
  - CPF ou CNPJ (ICP-Brasil)
  - Autoridade Certificadora
  - Período de validade do certificado
- Baseada em OpenSSL
- Arquitetura limpa (DTOs, Services, Exceptions)
- Compatível com PSR-4

---

### ❌ O que esta biblioteca NÃO faz

- Validação de cadeia ICP-Brasil
- Consulta OCSP ou LCR
- Validação de carimbo do tempo (TSA)
- Validação jurídica
- Verificação de confiabilidade da assinatura

---

### 🧱 Requisitos

- PHP **8.1 ou superior**
- Extensão OpenSSL habilitada
- PDF assinado no padrão **PAdES**

---

### 📦 Instalação

Via Composer:

```bash
composer require juancarlo99/pdf-pades-signature-extractor
```

---

### 🚀 Uso Básico

```php
use Juancarlo99\PdfPadesSignatureExtractor\Extractor\PadesExtractor;

$extractor = new PadesExtractor();
$assinatura = $extractor->extract('signed.pdf');

echo $assinatura->signerName;
echo $assinatura->cpf;
echo $assinatura->issuer;
```

---

### 📄 Licença

MIT License
