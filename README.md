# Edirect Erli Magento 2 Module

## Overview
Edirect_Erli is a Magento 2 extension that connects a store with the Erli marketplace. It synchronises catalog data, keeps order information aligned between platforms, and provides tooling for shipping-method mapping, cron maintenance, and logging so that Erli orders can be processed directly from Magento.【F:Edirect/Erli/etc/adminhtml/system.xml†L8-L188】【F:Edirect/Erli/Cron/CreateOrders.php†L19-L118】

## Requirements
- Magento 2 with the native modules required by the package (catalog, backend, cron, UI, etc.).【F:Edirect/Erli/composer.json†L5-L18】
- PHP 5.6.0, 7.0.2, or any 7.0.x release from 7.0.6 upward, as required by the composer package definition.【F:Edirect/Erli/composer.json†L5-L18】
- Access credentials for the Erli REST API (URL and token) so that the module can communicate with the marketplace.【F:Edirect/Erli/etc/adminhtml/system.xml†L12-L52】

## Installation
1. Copy the module into `app/code/Edirect/Erli` or install it through Composer with `composer require edirect/erli`.
2. From the Magento root directory run the usual maintenance commands:
   ```bash
   bin/magento module:enable Edirect_Erli
   bin/magento setup:upgrade
   bin/magento setup:di:compile
   bin/magento cache:flush
   ```
3. Configure the integration from the Magento Admin panel (see next section).

## Configuration
Open **Stores → Configuration → Edirect24 → Erli Integration** to adjust the settings exposed through `etc/adminhtml/system.xml`.

### General Configuration
- **API Url** and **API Key** – required to authenticate requests made by the helper clients.【F:Edirect/Erli/etc/adminhtml/system.xml†L20-L28】【F:Edirect/Erli/Helper/Erli.php†L63-L131】
- **CURL connection options** – enable SSL host verification and TCP keep-alive with optional idle/interval tuning.【F:Edirect/Erli/etc/adminhtml/system.xml†L28-L64】【F:Edirect/Erli/Helper/Erli.php†L63-L113】
- **Registered Hooks** – read-only list of webhook registrations returned from the API.【F:Edirect/Erli/etc/adminhtml/system.xml†L64-L70】
- **Check Erli API Connection** – runs a live API connectivity test from the admin UI.【F:Edirect/Erli/etc/adminhtml/system.xml†L70-L76】
- **Shipping Method Mapping** – opens the UI for mapping Erli delivery methods to Magento shipping carriers.【F:Edirect/Erli/etc/adminhtml/system.xml†L76-L84】

### Cron Configuration
- Configure the schedule expressions that drive order and product synchronisation jobs; defaults are `*/5 * * * *` for orders and `*/20 * * * *` for product updates.【F:Edirect/Erli/etc/adminhtml/system.xml†L84-L105】【F:Edirect/Erli/etc/crontab.xml†L7-L28】
- Trigger a one-off full product synchronisation by clicking **Set Product Full Synchronization Cron Job**.【F:Edirect/Erli/etc/adminhtml/system.xml†L100-L105】

### Erli Data
- Choose the store view that supplies prices, the source for simple product descriptions, and whether Magento price rules should be applied for exported offers.【F:Edirect/Erli/etc/adminhtml/system.xml†L105-L132】【F:Edirect/Erli/Helper/Data.php†L96-L210】

### Order and Delivery Status Mapping
Map Erli order lifecycle statuses to Magento order states, and configure delivery tracking vendors that should be reported back to Erli.【F:Edirect/Erli/etc/adminhtml/system.xml†L132-L179】

### Shipping Method Mapping Grid
Maintain the serialized list of Erli→Magento shipping mappings stored under `erli/shipping_method/mapping`. The module keeps a local table of Erli methods that can be synced via cron or the mapping dialog.【F:Edirect/Erli/etc/adminhtml/system.xml†L179-L188】【F:Edirect/Erli/etc/db_schema.xml†L8-L36】【F:Edirect/Erli/Cron/UpdateErliShippingMethod.php†L1-L120】

### Payment Configuration
Enable the bundled “Erli Payment” method and customise the label presented during checkout when orders are created from Erli data.【F:Edirect/Erli/etc/adminhtml/system.xml†L188-L203】【F:Edirect/Erli/etc/config.xml†L22-L34】

## Scheduled Tasks
The module registers the following cron jobs in the `edirect_erli_cron_group` group. Expressions can be overridden via the admin configuration where noted.【F:Edirect/Erli/etc/crontab.xml†L7-L28】【F:Edirect/Erli/etc/cron_groups.xml†L1-L14】

| Job | Default schedule | Purpose |
| --- | --- | --- |
| `edirect_erli_cronjob_create_orders` | Admin configurable (`erli/cron/order_synchro`) | Pull new orders from the Erli inbox and create Magento orders, retrying problematic ones stored locally.【F:Edirect/Erli/etc/crontab.xml†L8-L16】【F:Edirect/Erli/Cron/CreateOrders.php†L63-L118】 |
| `edirect_erli_cronjob_update_orders` | Admin configurable (`erli/cron/order_synchro`) | Synchronise order status updates back to Erli.【F:Edirect/Erli/etc/crontab.xml†L9-L16】【F:Edirect/Erli/Cron/UpdateOrders.php†L1-L160】 |
| `edirect_erli_cronjob_create_product` | `0 1 * * *` | Perform a full catalogue push to Erli.【F:Edirect/Erli/etc/crontab.xml†L16-L18】【F:Edirect/Erli/Cron/CreateProducts.php†L1-L200】 |
| `edirect_erli_cronjob_update_product` | Admin configurable (`erli/cron/update_products`) | Incrementally update product info (name, description, media, pricing, stock, attributes).【F:Edirect/Erli/etc/crontab.xml†L18-L22】【F:Edirect/Erli/Cron/UpdateProducts.php†L1-L120】 |
| `edirect_erli_cronjob_update_shipping_method` | `0 3 * * 0` | Refresh cached Erli delivery methods and COD flags.【F:Edirect/Erli/etc/crontab.xml†L22-L24】【F:Edirect/Erli/Cron/UpdateErliShippingMethod.php†L1-L120】 |
| `edirect_erli_cronjob_clear_logs` | `0 1 * * *` | Purge old integration logs from `ed_erli_log`.【F:Edirect/Erli/etc/crontab.xml†L24-L26】【F:Edirect/Erli/Cron/ClearLogs.php†L1-L120】 |
| `edirect_erli_cronjob_clear_problematic_orders` | `0 4 * * *` | Remove stale records from the `ed_erli_problematic_orders` queue once resolved.【F:Edirect/Erli/etc/crontab.xml†L26-L28】【F:Edirect/Erli/Cron/ClearProblematicOrders.php†L1-L120】 |

## Command Line Utilities
For manual maintenance you can trigger the cron handlers via dedicated console commands once the module is enabled.【F:Edirect/Erli/Console/Command/CreateOrdersCronCommand.php†L1-L67】【F:Edirect/Erli/Console/Command/UpdateOrdersCronCommand.php†L1-L71】【F:Edirect/Erli/Console/Command/CreateProductsCronCommand.php†L1-L66】【F:Edirect/Erli/Console/Command/UpdateProductsCronCommand.php†L1-L70】【F:Edirect/Erli/Console/Command/ClearLogsCronCommand.php†L1-L68】

```bash
bin/magento edirect:erli:create_orders
bin/magento edirect:erli:update_orders
bin/magento edirect:erli:create_products
bin/magento edirect:erli:update_products
bin/magento edirect:erli:clear_logs
```

## Web API Endpoint
The module exposes a public REST endpoint at `/V1/hooks/checkBuyAbility`. It allows Erli to call back into Magento to verify stock availability via the `HooksInterface::checkBuyAbility` service contract.【F:Edirect/Erli/etc/webapi.xml†L1-L11】【F:Edirect/Erli/Api/HooksInterface.php†L1-L20】

## Database Entities
Installation creates the following tables used for logging and synchronisation state tracking.【F:Edirect/Erli/etc/db_schema.xml†L1-L44】

- `ed_erli_log` – stores every API interaction (object name, method, response status/message).【F:Edirect/Erli/etc/db_schema.xml†L1-L19】【F:Edirect/Erli/Helper/Erli.php†L95-L144】
- `ed_erli_shipping_method` – caches Erli delivery methods so they can be mapped to Magento carriers.【F:Edirect/Erli/etc/db_schema.xml†L24-L36】
- `ed_erli_problematic_orders` – queues Erli order IDs that failed import for later retries.【F:Edirect/Erli/etc/db_schema.xml†L36-L44】【F:Edirect/Erli/Cron/CreateOrders.php†L108-L118】
- `quote`.`erli_id` / `sales_order`.`erli_id` – link Magento entities to the originating Erli order; `sales_order`.`erli_updated_at` stores the last sync timestamp.【F:Edirect/Erli/etc/db_schema.xml†L19-L32】

## Logging
Every API request is recorded via `Edirect\Erli\Model\Log`, providing visibility into the payloads exchanged with Erli and aiding troubleshooting.【F:Edirect/Erli/Model/Log.php†L1-L24】【F:Edirect/Erli/Helper/Erli.php†L95-L144】 You can monitor the integration by querying the `ed_erli_log` table or by enabling the cron commands in verbose mode.

## License
This package is distributed under the OSL-3.0 and AFL-3.0 licences as declared in `composer.json`.【F:Edirect/Erli/composer.json†L18-L24】
