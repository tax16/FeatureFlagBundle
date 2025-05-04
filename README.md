# FeatureFlagBundle 📊

> A Symfony feature flag bundle compatible with **PHP 8.2+** and **Symfony 6+**

The **FeatureFlagBundle** is a powerful tool designed to help you manage feature toggles across your application.  
It provides a flexible, extensible, and non-intrusive way to control feature behavior based on environment, context, or user role—without modifying or redeploying your codebase.

Enable or disable features using various data sources, caching layers, or custom providers—ideal for modern, modular applications.

---
## 🚀 Installation
**Add the bundle via Composer**  
Run the following command in your terminal:

   ```bash
   composer require tax16/feature-flag
   ```

## ⚙️ Features

- 🗂️ **Multiple Storage Backends**  
  Supports YAML, JSON, Doctrine, or any custom data provider for maximum flexibility.

- ⚡ **Efficient Caching**  
  Optional PSR-compliant cache layer in addition to internal static caching to boost performance.

- 🔌 **Custom Provider Integration**  
  Plug in your own feature flag provider by implementing `FeatureFlagProviderInterface`.

- 🧠 **Contextual Flag Evaluation**  
  Evaluate flags dynamically based on user roles, IPs, environments, or any custom logic.

- 🧩 **Attribute-Based Switching**  
  Use PHP attributes to toggle behavior at the method or class level with minimal intrusion.

- 🔀 **Controller Route Switching**  
  Conditionally activate routes using attributes like `#[FeaturesFlagSwitchRoute]`.

- 🚫 **Class and Method Blocking**  
  Prevent entire classes or methods from executing unless certain flags are active.

- 🔧 **Zero-Code Changes**  
  Toggle features across environments without modifying or redeploying your code.

## ⚙️ How It Works — FeatureFlag via Dynamic Proxy

This bundle leverages [`ocramius/proxy-manager`](https://github.com/Ocramius/ProxyManager) to create **dynamic proxies** around your services, enabling automatic feature flag evaluation using PHP attributes.

It provides a seamless and non-intrusive way to control service behavior without modifying the original class logic.

---

### 🧠 Behind the Scenes

Internally, classes like `SwitchClassProxyFactory` and `SwitchMethodProxyFactory` generate proxy wrappers for your services during the **Symfony compilation process**, ensuring optimal performance.

These proxies:
- Intercept `public` methods annotated with:
    - `#[FeatureFlagSwitchClass]`
    - `#[FeatureFlagSwitchMethod]`
    - `#[FeaturesFlagSwitchClass]`
    - `#[FeaturesFlagSwitchMethod]`

- Transparently delegate method calls based on feature flag state
- Use the original service instance, preserving the expected class type and behavior
- Support single or multiple flag evaluation
- Optionally apply context-aware logic (e.g. by IP, user, environment, etc.)

Everything is processed **at compile time** to avoid any runtime overhead.

## ⚙️ Configure

To configure the bundle, add the following to your Symfony configuration (e.g. `config/packages/feature_flags.yaml`):

```yaml
feature_flags:
  storage:
    type: doctrine         # Supported: 'doctrine', 'yaml', 'json'
    # path: '%kernel.project_dir%/config/feature_flags.yaml' 
    # Required only for 'yaml' or 'json' storage types

  cache: true              # Enables PSR cache in addition to built-in static caching
  ttl: 60                  # Time-to-live (in seconds) for the PSR cache

  # controller_check: true
  # Enable if you want to use feature flags on controllers
  # (e.g. with #[IsFeatureActive], #[IsFeatureInactive], #[FeaturesFlagSwitchRoute])

  # provider: App\FeatureFlag\MyCustomProvider
  # Use a custom provider by implementing FeatureFlagProviderInterface
```

ℹ️ **If you're using Doctrine, ensure the following:**

- Install the necessary Doctrine package.
- Configure the entity mappings in `config/packages/doctrine.yaml`.
- Copy the mapping code into your Doctrine mapping services.
- Create the required migration for your feature flag entities.

Example of Doctrine mapping configuration:

```yaml
mappings:
  FeatureFlagBundle:
    type: xml
    dir: '%kernel.project_dir%/vendor/tax16/feature-flag/src/Infrastructure/FeatureFlag/Resources/config/doctrine'
    prefix: 'Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Entity'
    is_bundle: false
    alias: FeatureFlagBundle
```

ℹ️ **If you want to use a custom provider** (e.g., integrating with Gitlab, LaunchDarkly, or your own custom storage):

- Edit the `feature_flags` configuration file.
- Set the `provider` option to point to your custom provider class.
- Ensure your custom provider class implements the `FeatureFlagProviderInterface`.

Example:

```yaml
feature_flags:
  provider: App\FeatureFlag\CustomGitlabProvider
```


### 🔁 Example: Using the `FeatureFlag` Bundle

#### Basic Example with a Provider:

```php
#[Route('/test', name: 'app_test')]
public function index(FeatureFlagProviderInterface $featureFlagProvider): Response
{
    // Single feature check
    $isFeatureActive = $featureFlagProvider->isFeatureActive('my_feature', [ContextService::class]);

    // Multiple feature check
    $areAllFeaturesActive = $featureFlagProvider->areAllFeaturesActive(['my_feature', 'my_second_feature'], [ContextService::class]);

    // By default, the context is empty, but you can customize the feature check with specific contexts, 
    // such as by IP, user role, etc.
}
```
#### Available Attributes for Feature Flag Management:

There are 7 attributes available to manage feature flags:

- `#[FeatureFlagSwitchClass]`: Switches the entire class behavior based on a feature flag.
- `#[FeatureFlagSwitchMethod]`: Switches a specific method in a class based on a feature flag.
- `#[FeaturesFlagSwitchClass]`: Similar to `#[FeatureFlagSwitchClass]`, but supports checking multiple feature flags.
- `#[FeaturesFlagSwitchMethod]`: Similar to `#[FeatureFlagSwitchMethod]`, but supports checking multiple feature flags.
- `#[FeaturesFlagSwitchRoute]`: Allows switching routes based on the activation of feature flags.
- `#[IsFeatureActive]`: Checks if a feature is active and allows custom logic based on this.
- `#[IsFeatureInactive]`: Checks if a feature is inactive and prevents certain actions based on this.

These attributes provide powerful, attribute-based feature flag management across classes, methods, routes, and more.

#### Example of Usage:

- **FeatureFlagSwitchMethod**:  
  The `#[FeatureFlagSwitchMethod]` attribute allows you to switch between methods based on the activation of a feature flag.

```php
class FlagService
{
    // This method will be switched to "helloWorldSwitch" if the feature flag "new_feature" is activated.
    #[FeatureFlagSwitchMethod(feature: 'new_feature', method: 'helloWorldSwitch')]
    public function helloWorld(): string
    {
        // Original behavior when "new_feature" is not active
        return 'Hello World!';
    }
    
    // This method will be executed when "new_feature" is active
    public function helloWorldSwitch(): string
    {
        // New behavior when the feature flag is activated
        return 'Hello New World!';
    }
}
```

- **FeaturesFlagSwitchClass**:  
  The `#[FeaturesFlagSwitchClass]` attribute allows you to replace the behavior of an entire class based on one or more feature flags. If the feature flag is activated, it delegates the method calls to another class.

```php
#[FeaturesFlagSwitchClass(features: ['new_feature'], switchedClass: FlagService::class)]
class FlagSwitchedService
{
    public function helloWorld(): string
    {
        // If "new_feature" is activated, the "helloWorld" method of FlagService will be called instead
        // of the method in FlagSwitchedService.
    }
}
```

- **FeaturesFlagSwitchClass with Context**:  
  The `#[FeaturesFlagSwitchClass]` attribute can also be used with a context to further control when the feature flag should switch the class behavior. The context allows you to specify conditions (like IP address, user role, etc.) that must be met for the class switch to occur.

  ⚠️ **Info**: The context classes should implement the `FeatureFlagContextInterface`.

```php
#[FeaturesFlagSwitchClass(features: ['new_feature'], switchedClass: FlagService::class, context: [IpContext::class])]
class FlagSwitchedService
{
    public function helloWorld(): string
    {
        // If "new_feature" is activated and the context condition (e.g., IP address) is met,
        // the "helloWorld" method from FlagService will be called instead of the one in FlagSwitchedService.
        // For example: only users with a specific internal IP can access the feature.
    }
}
```
ℹ️ Note: In this example, IpContext::class could be a context class that checks the user's IP address. The feature flag will only switch to FlagService if the new_feature flag is active and the condition in IpContext is satisfied (e.g., only internal users can access the feature).

- **FeaturesFlagSwitchClass with Filtered Method**:  
  The `#[FeaturesFlagSwitchClass]` attribute can be used with the `filteredMethod` option to specify that only certain methods should be switched based on the feature flag. This allows you to control which methods are affected by the flag while keeping other methods intact.

```php
#[FeaturesFlagSwitchClass(feature: 'new_feature', switchedClass: FlagService::class, filteredMethod: ["helloWorld"])]
class FlagSwitchedService
{
    // This method will be switched to "helloWorld" from FlagService if the "new_feature" is activated.
    public function helloWorld(): string
    {
        return 'This will be replaced by FlagService helloWorld method when the feature is active.';
    }
    
    // This method will remain unaffected, as it is not included in the filteredMethod list.
    public function helloWorld2(): string
    {
        return 'This method remains in FlagSwitchedService, unaffected by the feature flag.';
    }
}
```

- ✅ **Dependency Injection**:  
One challenge that often arises when using feature flags with class switching is how to switch between instances of different classes, like switching from **FlagSwitchedService** to **FlagService**, when the feature is activated. However, with this bundle, everything works seamlessly!
The **proxy** mechanism ensures that no new instance of **FlagService** is created. Instead, it wraps the **FlagSwitchedService** instance and delegates calls to the methods of **FlagService** when the feature is enabled. This allows for smooth transitions between service implementations without disrupting the existing dependency injection setup.

Here is an example of how the dependency injection works:

```php
final class FakeController extends AbstractController
{
    private readonly FlagSwitchedService $flagSwitched;

    public function __construct(FlagSwitchedService $flagSwitched)
    {
        // The FlagSwitchedService is injected into the controller as usual
        // Even if the feature flag switches the class behavior, the same instance is used.
        $this->flagSwitched = $flagSwitched;
    }

    // Your controller methods go here...
}
```
- **IsFeatureActive** | **IsFeatureInactive**:  
  You can use these attributes on both classes and methods to conditionally execute code based on feature flags. However, if you want to apply them to a **controller** method, you need to enable the `controller_check` option in your **feature_flags** configuration.

#### Example: Using `IsFeatureActive` at the class level or method

```php
#[IsFeatureActive(features: ['new_feature'], context: [ContextService::class], exception: FeatureFlagActiveException::class)]
class FakeService
{
    // All methods within this class will depend on the activation of the "new_feature".
    // If the feature is not active, it will throw the "FeatureFlagActiveException" (or any custom exception you specify).
}

// You can also use this on the method if you need to specify the only method to check

#[IsFeatureActive(features: ['new_feature'],  context: [ContextService::class], exception: FeatureFlagActiveException::class)]
public function helloWorldWithoutRetry(): string
{
    //....
}
```

> ⚠️ **Warning**:  
A **final** class, **abstract** class, or **controller** cannot use the method switch or class switch attributes (`FeatureFlagSwitchMethod` or `FeatureFlagSwitchClass`). These attributes are not applicable to these types of classes due to the restrictions on their instantiation and behavior.

- **FeaturesFlagSwitchRoute**:
To use the `FeaturesFlagSwitchRoute` attribute, you need to activate `controller_check` in your **feature_flags** configuration.

Here's an example of how to use the `FeaturesFlagSwitchRoute` attribute to switch routes dynamically based on feature flags:

```php
#[Route('/test', name: 'app_test')]
#[FeaturesFlagSwitchRoute(
        features: ['new_feature'], 
        switchedRoute: 'app_fake_2', 
        context: []  // Optionally, specify context such as user role, IP, etc.
    )
]
public function index(): Response
{
   // If "new_feature" is active, this route will be switched to 'app_fake_2'.
   // Otherwise, this method will be executed.
   return $this->render('test/index.html.twig');
}

#[Route('/fake2', name: 'app_fake_2')]
public function index2(): Response
{
   // This is the fallback route (when the feature is active).
   return $this->render('fake2/index.html.twig');
}
```

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
