PreProcessorFilter
==================

Simple pre-processor filter for phing.

Currently supports:
* #ifdef DEFINITION (#else) / #endif
* #ifndef DEFINITION (#else) / #endif

Definitions can consist of letters, numbers and underscores ([A-Za-z0-9_]).


Example of targets:

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

For now, only the names of the definitions (passed as parameters to the pre-processor filter) are used; their values are ignored.
All parameters are considered as definitions; the filter does not accept any parameter.

Example of file:

```php
function sendMail() {
#ifdef TESTING
    // do something for testing
#else
    mail(...);
#endif
}
```

It works for any files (PHP, CSS, JS, ..)