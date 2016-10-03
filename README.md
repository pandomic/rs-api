# RightSignature API
## Contents
* [Requirements](#requirements)
* [Installation](#installation)
* [Using](#using)
    * [Init RightSignature factory](#1-init-rightsignature-factory)
    * [Working with documents](#2-working-with-documents)
    * [Working with templates](#3-working-with-templates)
* [Exceptions handling](#3-exceptions-handling)
* [Request, Response, Conection. Use custom authorization](#4-request-response-conection-use-custom-authorization)

## Requirements
1. PHP >= 5.6
2. lib-libxml
3. Composer

## Installation
RS API can be installed via composer.

Add vcs repo to your `composer.json`:
```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/pandomic/rs-api"
    }
]
```

Add `pandomic/rs-api` dependency to your `composer.json`:
```json
"require": {
     "php": ">=5.6",
     "pandomic/rs-api": "0.1"
 }
```
Where `0.1` is a repo version that you want to use.

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
`load()` method will return `\StdClass()` contains documents data.

### 3. Working with Templates
#### 1. Init an empty Template model
```php
$template = $rs->template();
```
#### 2. Init and preload Template
```php
$template = $rs->template($guid);
```
Where `$guid` is RS template guid

#### 3. Loading template
Installing guid property
```php
$template->guid($guid)->load();
```
Passing guid using load() method
```php
$template->load($guid);
```
After loading template will be stored inside the Template object, so that you can use some methods without `$guid` specified:
```php
$template->load($guid);
// Do some actions
$template->load()->prepackage()->prefill($options)->get();
```
#### 4. Load templates list
You can get templates countable information by calling `getCount()` method:
```php
$info = $template->getCount();
// $info = [
//    'total_documents' => 'returns total templates count',
//    'total_pages'     => 'returns total pages count limited by 10 items per page'
// ]
```
Templates page can be obtained:
```php
$templates = $template->loadList();
// Or using extra parameters
$templates = $template->loadList($page, $perPage, $search);
```
Where `$page`- page number to fetch, `$perPage` - items count on page, `$search` - search string.

`loadList()` will return `array` of `\StdClass`.

#### 5. Prepackage Template(s)
```php
// Using single guid
$template->prepackage($guid);

// Using array of guids
$template->prepackage($guids);

// Prepackaging preloaded template
$template->load($guid)->prepackage();

// Prepackaging single/multiple templates with callback setting
$template->prepackage($guid|$guids, $callback);

// Prepackaging preloaded template with callback setting
$template->load($guid)->prepackage(null, $callback);
```
Where `$callback` is URL to post the document status

#### 6. Prefilling template
After prepackaging you may want to add/update some template info:
```php
// $options = [
//    'subject'           => 'document subject',
//    'description'       => 'document description',
//    'expires_in'        => 'document expiration date',
//    'callback_location' => 'document created callback URL',
//    'roles'             => [
//        [
//            'role_name' => 'role name to fill',
//            'role_id'   => 'role id to fill (use role_name or role_id)',
//            'name'      => 'name to fill the field with',
//            'email'     => 'email to fill the field with'
//        ],...
//    ],
//    'tags' => [
//        [
//           'name'  => 'tag name',
//           'value' => 'tag value (optional)'
//        ], ...
//    ],
//    'merge_fields' => [
//        [
//            'merge_field_id'   => 'field id to fill',
//            'merge_field_name' => 'field name to fill (use merge_field_id or merge_field_name)',
//            'value'            => 'field value',
//            'locked'           => 'lock field from changes'
//        ],...
//    ]
// ]
$template->prefill($options, $guid);

// Prefill previously loaded template
$template->load($guid)->prepackage()->prefill($options);
```
Not all of these `$options` must be set. See [RightSignature official API documentation](https://rightsignature.com/apidocs/api_documentation_default#/prefill_template) for more details.

#### 7. Get loaded template info
To get loaded template info simply call `get()` method:
```php
$content = $template->load($guid)->get();
```
`load()` method will return `\StdClass()` contains templates data.

#### 8. Prefill and send as document
Sending prepackaged templates as a documents is similar with prefilling:
```php
// $options = [
//    'subject'           => 'document subject',
//    'description'       => 'document description',
//    'expires_in'        => 'document expiration date',
//    'callback_location' => 'document created callback URL',
//    'roles'             => [
//        [
//            'role_name' => 'role name to fill',
//            'role_id'   => 'role id to fill (use role_name or role_id)',
//            'name'      => 'name to fill the field with',
//            'email'     => 'email to fill the field with'
//        ],...
//    ],
//    'tags' => [
//        [
//           'name'  => 'tag name',
//           'value' => 'tag value (optional)'
//        ], ...
//    ],
//    'merge_fields' => [
//        [
//            'merge_field_id'   => 'field id to fill',
//            'merge_field_name' => 'field name to fill (use merge_field_id or merge_field_name)',
//            'value'            => 'field value',
//            'locked'           => 'lock field from changes'
//        ],...
//    ]
// ]
$document = $template->prefillAndSend($options, $guid);

// Prefill and send previously loaded template
$document = $template->load($guid)->prepackage()->prefillAndSend($options);

// You can then use the `$document` to load the document and use it
$document->load();
$links = $document->getSignerLinks();
```
`prefillAndSend()` method will return the empty `Document` instance (with loaded guid but not preloaded, so that you can load document later). See [RightSignature official API documentation](https://rightsignature.com/apidocs/api_documentation_default#/prefill_template) for more options details.

#### 9. Swap templates underlying data
```php
// $options = [
//    'tags' => [
//        [
//           'name'  => 'tag name',
//           'value' => 'tag value (optional)'
//        ], ...
//    ],
//    'roles'             => [
//        [
//            'role_name' => 'role name to fill',
//            'role_id'   => 'role id to fill (use role_name or role_id)',
//            'name'      => 'name to fill the field with',
//            'email'     => 'email to fill the field with'
//        ],...
//    ],
//    'merge_fields' => [
//        [
//            'merge_field_id'   => 'field id to fill',
//            'merge_field_name' => 'field name to fill (use merge_field_id or merge_field_name)',
//            'value'            => 'field value',
//            'locked'           => 'lock field from changes'
//        ],...
//    ]
//    'subject'           => 'document subject',
//    'expires_in'        => 'document expiration date',
//    'description'       => 'document description',
//    'callback_location' => 'callback url after document will be signed'
// ]
$document = $template->swapDocument($documentPath, $options, $guid);
// Swap already preloaded template
$document = $template->load($guid)->swapDocument($documentPath, $options);
```
Where `$documentPath` is a local document path to upload. `swapDocument()` will return an empty `Document` instance (with  guid but not preloaded). 

See [RightSignature official API documentation](https://rightsignature.com/apidocs/api_documentation_default#/swap_underlying_pdf) for more details.

## 3. Exceptions handling

> Documentation in progress

## 4. Request, Response, Conection. Use custom authorization.

> Documentation in progress
