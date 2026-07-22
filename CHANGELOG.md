# Changelog

All notable changes to this project are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-07-22

### Added
- Unified payment abstraction: `PaymentManager` (Manager/Factory) + `Payment` facade.
- Gateway contracts with interface segregation: `PaymentGateway`, `SupportsRefund`, `SupportsQuery`.
- Immutable DTOs: `ChargeRequest`, `ChargeResponse`, `RefundRequest`, `RefundResponse`, `WebhookPayload`.
- `Money` value object using integer minor units (zero-decimal aware for VND/JPY).
- Enums: `Currency`, `PaymentStatus`, `PaymentMethod`.
- VNPay reference driver: charge (redirect) + HMAC-SHA512 webhook verification with constant-time comparison.
- Auto-registered webhook route `POST /payment/webhook/{gateway}` dispatching `PaymentCompleted` / `PaymentFailed`.
- Publishable config, service provider auto-discovery.
- Tooling: PHPStan (level max), Pint (PSR-12 + strict types), Pest (33 tests, 99.3% coverage), Docker toolchain, GitHub Actions CI.

[Unreleased]: https://github.com/tamfuldev/payment-gateway/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/tamfuldev/payment-gateway/releases/tag/v0.1.0
