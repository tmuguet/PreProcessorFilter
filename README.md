# PreProcessorFilter

Simple pre-processor filter for phing.

Currently supports:
* If / Else if:
    * `#if DEFINITION` : succeeds if `DEFINITION` is set and is true (in the PHP way)
    * `#ifdef DEFINITION` : succeeds if `DEFINITION` is set
    * `#ifndef DEFINITION` : succeeds if `DEFINITION` is not set
    * `#elif DEFINITION`
    * `#elifdef DEFINITION`
    * `#elifndef DEFINITION`
    * `#endif`
* Macro calls:
    * `#call myMacro (arg1, arg2, ...)`


## Usage

### Phing integration
#### Filter-reader
The pre-processor filter is a filter reader, which can be used in a filterchain in phing.

Examples of targets:

```xml
<target name="deploy_testing">
    <copy todir="${deploy_testing.dir}">
        <fileset refid="app"/>
        <filterchain>
            <filterreader classname="path.to.PreProcessorFilter">
                <param name="TESTING" value="1" />
            </filterreader>
        </filterchain>
    </copy>
</target>

<target name="deploy_staging">
    <copy todir="${deploy_staging.dir}">
        <fileset refid="app"/>
        <filterchain>
            <filterreader classname="path.to.PreProcessorFilter">
                <param name="STAGING" value="1" />
            </filterreader>
        </filterchain>
    </copy>
</target>
```

#### Parameters

Parameters can be passed to the pre-processor via `param` :
* `macrodir` (optional): directory containing macro definitions (see Macros)

Any other parameter will be considered as a definition (see Definitions).

### Directives in files

The pre-processor works on any files (PHP, CSS, JS, ...):

Example of file in PHP:

```php
function sendMail() {
#ifdef TESTING
    // do something for testing
#else
    mail(...);
#endif
}
```

In JavaScript:

```js
function debug(var) {
#ifdef TESTING
    alert(var);
#else
    // ignore
#endif
}
```

The pre-processor accepts directives which are indented:

```php
function sendMail() {
    #ifdef TESTING
        // do something for testing
    #else
        mail(...);
    #endif
}
```

Pre-processor directives MUST be alone on a line: the behavior is undefined and subject to change between versions.

For instance, the pre-processor does NOT work with multiple directives on the same line, or with some code on the same line:

```php
#ifdef TESTING #call myMacro() #endif   // will NOT work

myFunctionCall(); #call myMacro(arg1)   // will NOT work

#ifdef TESTING doSometing();            // will NOT work
#endif                                  //
```

## Definitions

Definitions can consist of letters, numbers and underscores (`[A-Za-z0-9_]`).
Examples:
* `TESTING`
* `DEBUG`
* ...

The definitions are case-sensitive, so `TESTING` and `testing` are different definitions.

## Macros

### What is a macro
Macros work as C-macros:

Using the following macro `viewGuard`:
```php
if (!isset(${var})) {
    throw new View_Exception("Variable {var} not set");
}
```

Calling the macro:
```php
<?php
#call viewGuard(id)
// My treatment
````
will result in:
```php
<?php
if (!isset($id)) {
    throw new View_Exception("Variable id not set");
}
// My treatment
```

### Defining macros

Each macro is defined in a specific file inside the `macrodir` directory. 
The macro `viewGuard` will be defined in the file `viewGuard.php`. 
The macro names can consist of letters, numbers and underscores (`[A-Za-z0-9_]`). 
They are case-sensitive, so `viewGuard` and `viewguard` are different macros (respectively defined in `viewGuard.php` and `viewguard.php`).

The format of a macro definition is basic:
* The first line is the list of arguments that the macro accepts, comma-separated
* The rest of the file is the content of the macro.

Examples:
```php
var
if (!isset(${var})) {
    throw new View_Exception("Variable {var} not set");
}
```

```php
var, message
if (!isset(${var})) {
    throw new View_Exception("Variable {var} not set: {message}");
}
```

When calling a macro, the count of the calling arguments must match the one defined and must be in the same order.
