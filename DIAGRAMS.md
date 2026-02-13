# Architecture Diagrams

## Before vs After Architecture

### Before: Monolithic Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         Request Flow                             │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    RemoveMagentoInitScripts                      │
│  ┌───────────────────────────────────────────────────────────┐ │
│  │ • Direct ObjectManager::getInstance()                      │ │
│  │ • Direct $_GET access (unsafe)                            │ │
│  │ • Regex HTML parsing (fragile)                            │ │
│  │ • @header() with error suppression                        │ │
│  │ • No input validation                                     │ │
│  │ • Hardcoded configuration paths                           │ │
│  └───────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
                      Response (HTML Modified)

Issues:
❌ Security vulnerabilities
❌ Not testable
❌ Tight coupling
❌ No separation of concerns
```

### After: Service-Oriented Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         Request Flow                             │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    RemoveMagentoInitScripts                      │
│                       (Plugin/Controller)                        │
│  ┌───────────────────────────────────────────────────────────┐ │
│  │ Dependencies (Dependency Injection):                       │ │
│  │ • RequestInterface                                         │ │
│  │ • ConfigurationProvider                                    │ │
│  │ • HtmlProcessor                                            │ │
│  │ • RequestValidator                                         │ │
│  │ • ResponseHeaderService                                    │ │
│  └───────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
            │                │              │                │
            ▼                ▼              ▼                ▼
    ┌─────────────┐  ┌─────────────┐  ┌──────────────┐  ┌─────────────┐
    │Configuration│  │   HTML      │  │   Request    │  │  Response   │
    │  Provider   │  │  Processor  │  │  Validator   │  │   Header    │
    │             │  │             │  │              │  │   Service   │
    │ • Config    │  │ • DOM Parse │  │ • Whitelist  │  │ • Safe      │
    │   access    │  │ • UTF-8     │  │ • Type check │  │   headers   │
    │ • Allowed   │  │ • Fallback  │  │ • CSRF ready │  │ • Error     │
    │   pages     │  │ • Logging   │  │ • Audit log  │  │   handling  │
    └─────────────┘  └─────────────┘  └──────────────┘  └─────────────┘
            │                │              │                │
            └────────────────┴──────────────┴────────────────┘
                                │
                                ▼
                      Response (Secure & Valid)

Benefits:
✅ Security hardened
✅ Fully testable
✅ Loose coupling
✅ Clear separation of concerns
```

## Service Layer Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         Service Layer                            │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────────┐
│ ConfigurationProvider│
├──────────────────────┤
│ + isReactEnabled()   │
│ + isVueEnabled()     │
│ + isJunkRemovalEnabled()│
│ + isDeferJsEnabled() │
│ + isCssRemovalEnabled()│
│ + isCriticalCssEnabled()│
│ + isDeferCssEnabled()│
│ + isPageTypeAllowed()│
│ + getAllowedPageTypes()│
│ + getConfigValue()   │
└──────────────────────┘
         │
         │ Uses
         ▼
┌──────────────────────┐
│ ScopeConfigInterface │
│ (Magento Core)       │
└──────────────────────┘

┌──────────────────────┐
│   HtmlProcessor      │
├──────────────────────┤
│ + removeMagentoInitScripts()│
│ + moveScriptsToBottom()│
│ + isValidHtml()      │
│ - parseWithDOM()     │
│ - fallbackToRegex()  │
└──────────────────────┘
         │
         │ Uses
         ▼
┌──────────────────────┐
│ DOMDocument          │
│ (PHP Core)           │
└──────────────────────┘

┌──────────────────────┐
│  RequestValidator    │
├──────────────────────┤
│ + getValidatedBoolParam()│
│ + isParameterOverrideAllowed()│
│ - validateWhitelist()│
│ - checkPermissions() │
└──────────────────────┘
         │
         │ Uses
         ▼
┌──────────────────────┐
│SessionManagerInterface│
│ (Magento Core)       │
└──────────────────────┘

┌──────────────────────┐
│ResponseHeaderService │
├──────────────────────┤
│ + setHeader()        │
│ + setServerTiming()  │
│ + setCustomHeaders() │
│ - checkHeadersSent() │
│ - logError()         │
└──────────────────────┘
         │
         │ Uses
         ▼
┌──────────────────────┐
│   HttpInterface      │
│ (Magento Core)       │
└──────────────────────┘
```

## Request Processing Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    HTTP Request Arrives                          │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                   Magento Request Handling                       │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│              Plugin: RemoveMagentoInitScripts                    │
│              (afterGetContent on HttpResponse)                   │
└─────────────────────────────────────────────────────────────────┘
                                │
                    ┌───────────┴───────────┐
                    │                       │
                    ▼                       ▼
        ┌───────────────────┐   ┌───────────────────┐
        │ Get Configuration │   │ Validate Request  │
        │                   │   │   Parameters      │
        │ ConfigProvider    │   │                   │
        │ .isJunkRemoval    │   │ RequestValidator  │
        │ Enabled()         │   │ .getValidated     │
        │                   │   │ BoolParam()       │
        └───────────────────┘   └───────────────────┘
                    │                       │
                    └───────────┬───────────┘
                                ▼
                    ┌───────────────────────┐
                    │  Should Optimize?     │
                    │                       │
                    │  • Check page type    │
                    │  • Check config       │
                    │  • Check parameters   │
                    └───────────────────────┘
                                │
                    ┌───────────┴───────────┐
                    │                       │
            Yes     ▼                       ▼  No
        ┌───────────────────┐       ┌──────────────┐
        │ Process HTML      │       │ Return       │
        │                   │       │ Original     │
        │ HtmlProcessor     │       │ Content      │
        │ .removeMagento    │       └──────────────┘
        │ InitScripts()     │
        └───────────────────┘
                    │
                    ▼
        ┌───────────────────┐
        │ Set Performance   │
        │ Headers           │
        │                   │
        │ ResponseHeader    │
        │ Service           │
        │ .setServerTiming()│
        └───────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────────────────────────────┐
│                  Modified Response Returned                      │
└─────────────────────────────────────────────────────────────────┘
```

## Data Flow Diagram

```
┌────────────┐
│   Config   │
│  Database  │
└─────┬──────┘
      │
      ▼
┌─────────────────┐      ┌──────────────┐
│ Configuration   │─────▶│   Plugin/    │
│   Provider      │      │  Observer    │
└─────────────────┘      └──────┬───────┘
                                │
┌────────────┐                  │
│  Request   │                  │
│ Parameters │──────────────────┤
└────────────┘                  │
      │                         │
      ▼                         ▼
┌─────────────────┐      ┌──────────────┐
│   Request       │─────▶│   Business   │
│  Validator      │      │    Logic     │
└─────────────────┘      └──────┬───────┘
                                │
┌────────────┐                  │
│    HTML    │                  │
│  Content   │──────────────────┤
└────────────┘                  │
      │                         │
      ▼                         ▼
┌─────────────────┐      ┌──────────────┐
│     HTML        │─────▶│  Processed   │
│   Processor     │      │   Content    │
└─────────────────┘      └──────┬───────┘
                                │
                                ▼
                         ┌──────────────┐
                         │   Response   │
                         │   Headers    │
                         └──────┬───────┘
                                │
                                ▼
                         ┌──────────────┐
                         │ HTTP Response│
                         └──────────────┘
```

## Security Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        Security Layers                           │
└─────────────────────────────────────────────────────────────────┘

Layer 1: Input Validation
┌─────────────────────────────────────────────────────────────────┐
│  RequestValidator                                                │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │ • Whitelist-based validation                               │ │
│  │ • Type checking                                            │ │
│  │ • Permission checks                                        │ │
│  │ • CSRF token validation (ready)                            │ │
│  └────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
Layer 2: Business Logic
┌─────────────────────────────────────────────────────────────────┐
│  Service Layer                                                   │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │ • Configuration validation                                 │ │
│  │ • Page type filtering                                      │ │
│  │ • Safe HTML processing                                     │ │
│  │ • Audit logging                                            │ │
│  └────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
Layer 3: HTML Processing
┌─────────────────────────────────────────────────────────────────┐
│  HtmlProcessor                                                   │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │ • DOM-based parsing (prevents injection)                   │ │
│  │ • UTF-8 encoding (prevents encoding attacks)               │ │
│  │ • Fallback mechanism                                       │ │
│  │ • Error handling                                           │ │
│  └────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
Layer 4: Output Security
┌─────────────────────────────────────────────────────────────────┐
│  ResponseHeaderService                                           │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │ • Safe header setting                                      │ │
│  │ • Headers-sent check                                       │ │
│  │ • No direct user input                                     │ │
│  │ • Comprehensive error handling                             │ │
│  └────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

## Testing Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        Testing Pyramid                           │
└─────────────────────────────────────────────────────────────────┘

                            ┌───────┐
                            │  E2E  │ (Future)
                            └───────┘
                          ┌─────────────┐
                          │ Integration │ (Future)
                          └─────────────┘
                      ┌─────────────────────┐
                      │    Unit Tests       │ ✅
                      │  ┌───────────────┐  │
                      │  │Configuration  │  │
                      │  │   Provider    │  │
                      │  └───────────────┘  │
                      │  ┌───────────────┐  │
                      │  │     HTML      │  │
                      │  │   Processor   │  │
                      │  └───────────────┘  │
                      │  ┌───────────────┐  │
                      │  │    Request    │  │
                      │  │   Validator   │  │
                      │  └───────────────┘  │
                      └─────────────────────┘

Current Coverage: Service Layer (100%)
Next Phase: Plugin/Observer Integration Tests
```

## Dependency Graph

```
┌─────────────────────────────────────────────────────────────────┐
│                      Dependency Flow                             │
└─────────────────────────────────────────────────────────────────┘

RemoveMagentoInitScripts
    │
    ├─→ RequestInterface (Magento)
    ├─→ ConfigurationProvider
    │       └─→ ScopeConfigInterface (Magento)
    ├─→ HtmlProcessor
    │       └─→ LoggerInterface (PSR-3)
    ├─→ RequestValidator
    │       └─→ SessionManagerInterface (Magento)
    └─→ ResponseHeaderService
            └─→ LoggerInterface (PSR-3)

DeferJS (Observer)
    │
    ├─→ RequestInterface (Magento)
    ├─→ ConfigurationProvider
    ├─→ HtmlProcessor
    └─→ RequestValidator

All dependencies are:
✅ Injected via constructor
✅ Interface-based (mockable)
✅ Framework-provided or custom services
❌ No ObjectManager
❌ No direct instantiation
```

## Legend

```
Symbols Used:
│ ├ └ ┌ ┐ ─  : Flow/Connection lines
▼           : Direction of flow
✅          : Completed/Available
❌          : Not used/Removed
→           : Depends on
```
