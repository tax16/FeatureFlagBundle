
# FeatureFlagBundle 📊

> A Symfony feature flag bundle compatible with PHP 8.2+ and symfony 6+

> The Feature Flag Bundle is a powerful tool designed to help you manage feature toggles across your application. It provides a flexible and extensible way to control feature access and behavior in different environments or user contexts, without needing to modify or redeploy your codebase. You can easily enable or disable features in your application using various data sources, caching mechanisms, and custom providers, making it a versatile solution for modern application architectures.

## 🚀 Installation
**Add the bundle via Composer**  
Run the following command in your terminal:

   ```bash
   composer require tax16/feature-flag
   ```

## ⚙️ Features

- **Data Sources**: Configure flags with YAML, JSON, or Doctrine.

- **Caching**: Optionally enable caching for improved performance.

- **Custom Provider**: Integrate your own flag provider.

- **Context-Based**: Use flags in custom contexts (e.g., users, roles, environments).

- **Switch Methods**: Use flags via method, class, or conditional logic.

Effortlessly control feature access across environments without code changes.
## ⚙️ How It Works — FeatureFlag via Dynamic Proxy

This bundle uses [`ocramius/proxy-manager`](https://github.com/Ocramius/ProxyManager) to dynamically intercept method calls and apply **automatic check feature flag logic** based on PHP attributes.

### 🧠 Behind the Scenes

A dedicated class, `SwitchClassProxyFactory|SwitchMethodProxyFactory`, creates a **dynamic proxy** around any service. This proxy:

- Intercepts **public methods annotated** with the attributes:
  - `#[FeatureFlagSwitchClass]`
  - `#[FeatureFlagSwitchMethod]`
  - `#[FeaturesFlagSwitchClass]`
  - `#[FeaturesFlagSwitchMethod]`
- Duplicate and custom class or function called
- Verify if parameter is compatible or not
- The `injected` service always keeps the `same instance type` — only the internal logic changes, not the class itself.

This behavior is completely transparent to your application code.

### 🔁 Example: Using the `FeatureFlag` Attribute

- **FeatureFlagSwitchMethod**:
```php
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchMethod;

class FlagService
{
    #[FeatureFlagSwitchMethod(feature: 'new_feature', method: 'helloWorldSwitch')]
    public function helloWorld(): string
    {
        // this will call the function helloWorldSwitch if "new_feature" is activated
    }
    
     public function helloWorldSwitch(): string
    {
        // ....
    }
}
```

- **FeatureFlagSwitchClass**:
```php
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeaturesFlagSwitchClass;

#[FeaturesFlagSwitchClass(features: ['new_feature'], switchedClass: FlagService::class)]
class FlagSwitchedService
{
    public function helloWorld(): string
    {
        // this will call the function "helloWorld" of class FlagService::class instead
        // of FlagSwitchedService if features is activated
    }
}
```

-**FeatureFlagSwitchClass** with context:
```php
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeaturesFlagSwitchClass;

#[FeaturesFlagSwitchClass(feature: 'new_feature', switchedClass: FlagService::class, context: [IpContext::class])]
class FlagSwitchedService
{
    public function helloWorld(): string
    {
        // this will call the function "helloWorld" of class FlagService::class
        // instead of FlagSwitchedService if features activate and if IpContext allowed to switch
        // for example: only internal user is able to check the feature
    }
}
```

-**FeatureFlagSwitchClass** with filteredMethod:
```php
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeaturesFlagSwitchClass;

#[FeaturesFlagSwitchClass(feature: 'new_feature', switchedClass: FlagService::class, filteredMethod: ["helloWorld"])]
class FlagSwitchedService
{
    public function helloWorld(): string
    {
        // this will call the function "helloWorld" of class FlagService::class
        // instead of FlagSwitchedService if features activate
    }
    
    public function helloWorld2(): string
    {
        // this will be called, no switched function, we use filteredMethod
    }
}
```

-**dependancy injection**:
One of the challenges we faced was switching from an instance of FlagSwitchedService to FlagService when the feature is activated.
No worries though — everything works seamlessly. The proxy doesn’t create a new instance of FlagService; instead, it wraps FlagSwitchedService and delegates calls to the FlagService methods when needed.
```php
// ...
final class FakeController extends AbstractController
{
    public function __construct(
    private readonly FlagSwitchedService $flagSwitched
    ) {}
    // ....
}
```

## ⚙️ Configure
```yaml
feature_flags:
  storage:
    type: json  # Can be 'doctrine', 'yaml' ou 'json'
    path: '%kernel.project_dir%/config/feature_flags.json'
  cache: true # We have already a static cache for multiple call, but this will add psr cache
```

> ℹ️ If you're using Doctrine, make sure to:
> - Configure the entity mappings
>   - Copy this code to your doctrine mapping services
```xml
<?xml version="1.0" encoding="UTF-8"?>

<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://doctrine-project.org/schemas/orm
                                      http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">
    <entity name="Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Entity\FeatureFlag" table="feature_flag">
        <id name="name" type="string">
            <generator strategy="NONE"/>
        </id>
        <field name="enabled" type="boolean"/>
        <field name="startDate" type="datetime" nullable="true"/>
        <field name="endDate" type="datetime" nullable="true"/>
    </entity>
</doctrine-mapping>
```

> - Install the appropriate Doctrine package

## 🤝 Contributing

> The application is designed in hexagonal architecture:

![Network design](doc/img/hexagonal.png)

> To contribute to the SystemCheckBundle, follow these steps:

1. **Clone the repository**:
   ```bash
   git clone https://github.com/tax16/FeatureFlagBundle
   ```

2. **Install dependencies**:
   ```bash
   make install
   ```

3. **Run GrumPHP for code quality checks**:
   ```bash
   make grumphp
   ```

4. **Run tests**:
   ```bash
   make phpunit
   ```

Happy coding! 🎉
