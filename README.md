# RightSignature API
## Contents
* [Installation](#installation)
* [Using](#using)
    * [Init RightSignature factory](#1-init-rightsignature-factory)
    * [Working with documents](#2-working-with-documents)
    * [Working with templates](#3-working-with-templates)
* [Exceptions handling](#4-exceptions-handling)
* [Request, Response, Conection. Use custom authorization](#5-request-response-conection-use-custom-authorization)

## Installation
> Documentation in progress

## Using
### 1. Init RightSignature factory
```php
use RightSignature\RightSignature;
$rs = new RightSignature($token);
```
Where `$token` is your RightSignature API token.

### 2. Working with documents
#### 1. Init an empty Document model
```php
$document = $rs->document();
```
#### 2. Init and preload document
```php
$document = $rs->document($guid);
```
Where `$guid` is RS document guid.

#### 3. Loading document
Installing guid property
```php
$document->guid($guid)->load();
```
Passing guid using load() method
```php
$document->load($guid);
```
After loading document will be stored inside the Document object, so that you can use some methods without `$guid` specified:
```php
$document->load($guid);
// Do some actions
$document->load();
```
#### 4. Load documents list
You can get documents countable information by calling `getCount()` method:
```php
$info = $document->getCount();
// $info = [
//    'total_documents' => 'returns total documents count',
//    'total_pages'     => 'returns total pages count limited by 10 items per page'
// ]
```
Documents page can be obtained:
```php
$documents = $document->loadList();
// Or using extra parameters
$documents = $document->loadList($page, $perPage, $state, $search);
```
Where `$page`- page number to fetch, `$perPage` - items count on page, `$state` - fetch only documents with specified state, `$search` - search string.

`loadList()` will return `array` of `\StdClass`.
#### 5. Get info for multiple documents
Sometimes you may need to fetch info for multiple documents. You can fetch info for up to 20 documents:
```php
$info = $document->batchDetails($guids);
```
Where `$guids` - array of documents guids.

#### 6. Trash document
To delete document just run:
```php
$status = $document->trash($guid);
```
You may also delete preloaded document by calling `trash()` without arguments:
```php
$status = $document->load($guid)->trash();
```
`trash()` method will return `boolean` operation status.

#### 7. Extend documents expiration
To extend documents expiration to 7 days you will need to write:
```php
$status = $document->extendExpiration($guid);
// For earlier preloaded document
$status = $document->load($guid)->extendExpiration();
```
`extendExpiration()` method will return `boolean` operation status.

#### 8. Update documents tags
Add the following lines:
```php
// $tags = [
//    [
//       'name'  => 'tag name',
//       'value' => 'tag value (optional)'
//    ], ...
// ]
$status = $document->updateTags($tags, $guid);
// For earlier preloaded document
$status = $document->load($guid)->updateTags($tags);
```

#### 9. Update documents callback
```php
$status = $document->updateCallback($callback, $guid);
// For earlier preloaded document
$status = $document->load($guid)->updateCallback($callback);
```
Where `$callback` is any url to process callback request.
`updateCallback()` method will return `boolean` operation status.

#### 10. Get documents signer links
To get `array` of documents signer links:
```php
$links = $document->getSignerLinks($guid);
// For earlier preloaded document
$links = $document->load($guid)->getSignerLinks();
```
#### 10. Send document
See [RightSignature official API documentation](https://rightsignature.com/apidocs/api_documentation_default#/send_document) for options details.

To send document you'll need to add a few lines of code (full example):
```php
// Not all of these options must be used, see api docs for more info
// $options = [
//    'recipients' => [
//        [
//            'name'      => 'recipient name',
//            'email'     => 'recipient email',
//            'role'      => 'recipient role',
//            'locked'    => 'prevent changes',
//            'is_sender' => 'recipient is sender'
//        ],...
//    ],
//    'tags' => [
//        [
//           'name'  => 'tag name',
//           'value' => 'tag value (optional)'
//        ], ...
//    ],
//    'subject'           => 'document subject',
//    'expires_in'        => 'document expiration date',
//    'description'       => 'document description',
//    'callback_location' => 'callback url after document will be signed',
//    'use_text_tags'     => 'use text tags',
//    'lock_signers'      => 'prevent changing of signers',
//    'passcode_question' => 'sequrity question',
//    'passcode_answer'   => 'sequrity answer'
// ]
$status = $document->send($documentPath, $options);
```
Where `$documentPath` is document local path.

`send()` method will return `boolean` operation status.
#### 11. Get loaded document info
To get loaded document info simply call `get()` method:
```php
$content = $document->load($guid)->get();
```
`load()` method will return `\StdClass()' contains documents data.

### 3. Working with Templates

> Documentation in progress

## 3. Exceptions handling

> Documentation in progress

## 4. Request, Response, Conection. Use custom authorization.

> Documentation in progress
