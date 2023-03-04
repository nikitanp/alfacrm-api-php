# API для AlfaCRM

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nikitanp/alfacrm-api-php.svg?style=flat-square)](https://packagist.org/packages/nikitanp/alfacrm-api-php)

---
## Описание

Данный пакет является PHP клиентом для [REST API AlfaCRM](https://alfacrm.pro/rest-api)

Клиент использует [PSR-18](https://www.php-fig.org/psr/psr-18/) и [PSR-17](https://www.php-fig.org/psr/psr-17/) для своей работы.
Например, можно использовать в качестве http клиента библиотеку [guzzlehttp/guzzle](https://github.com/guzzle/guzzle) 
и PSR-17 имплементацию для нее [http-interop/http-factory-guzzle](https://github.com/http-interop/http-factory-guzzle).

## Установка

```bash
composer require nikitanp/alfacrm-api-php
```

## Базовые методы для работы

Базовые методы находятся в классе `\Nikitanp\AlfacrmApiPhp\Entities\AbstractEntity`:

- ```get(int $page = 0, array $filterData = []): array``` Возвращает одну страницу данных с возможностью фильтрации
- ```getAll(array $filterData = []): \Generator``` Возвращает все сущности с возможностью фильтрации.
- ```getFirst(array $filterData = []): array``` Возвращает первый элемент.
- ```count(array $filterData = []): int``` Возвращает количество результатов с указанным фильтром.
- ```fields(array $filterData = []): array``` Возвращает возможные поля. Для получения результата берется первый ответ из системы.
- ```create(array $entityData): array``` Создает сущность
- ```update(int $entityId, array $updateData): array``` Обновляет сущность
- ```delete(int $entityId): array``` Удаляет сущность

## Пример использования

```php
$apiClient = new \Nikitanp\AlfacrmApiPhp\Client(
     $psr18Client,
     $psr17RequestFactory,
     $psr17StreamFactory
);
$apiClient->setDomain('domain.alfacrm.pro');
$apiClient->setEmail('admin@domain.exaple');
$apiClient->setApiKey('application-api-key');
$apiClient->authorize();

$customer = new \Nikitanp\AlfacrmApiPhp\Entities\Customer($apiClient);
$customer->fields();
$customer->count();
$customer->get();
$customer->getAll();
$customer->getAllArchived();
$customer->create(['customer_data']);
$customer->delete(1);
$customer->update(1, ['customer_data']);
```
