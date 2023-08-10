CHANGELOG
=========

## v0.1.15

- Support PHP 8.0 ~ 8.2
- Support `elasticsearch/elasticsearch 8.*`

## v0.1.14

- Add missing `_routing` parameter

## v0.1.13

- Update support PHP versions: 5.6 ~ 7.4

## v0.1.12

- Add `IndexNameProvider` to provide valid index names
- Add `CacheItemPoolInterface` to `Manager` as a dependency
- Rename method name `Manager::updateTemplate` to `Manager::updateIndexTemplate`
- Remove `Nadia\ElasticSearchODM\ElasticSearch\Client` and `Nadia\ElasticSearchODM\ElasticSearch\ClientBuilder`

## v0.1.11

- Fix missing "properties" in ESObject mapping

## v0.1.10

- Rename mapping Object to ESObject
- Fix PHP version compatibility

## v0.1.9

- Add "Object" mapping annotation and refactor the process of resolving "@ES\Column" property

## v0.1.8

- Allow wildcard in index names

## v0.1.7

- Fix bug when accessing non-public property values

## v0.1.6

- Fix invalid template pattern
- Fix update template parameters when settings is empty

## v0.1.5

- Move "updateTemplate" method from Repository to Manager
- Support fixed/dynamic template name
  
  Dynamic template name can add env name by replacing with `%s` string

## v0.1.4

- Add template name prefix with env name

## v0.1.3

- Add Number and Text annotations

## v0.1.2

- Update property visibility for Manager class
- Remove $client parameter's type declaration (for Manager class)

## v0.1.1

- Add missing test for Client class
- Add methods to enable/disable ClassMetadataLoader cache

## v0.1.0

- Complete basic functions
