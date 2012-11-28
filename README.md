PreProcessorFilter
==================

Simple pre-processor filter for phing.

Currently supports:
* #ifdef DEFINITION (#else) / #endif
* #ifndef DEFINITION (#else) / #endif


Example of targets:

```xml
<target name="deploy_testing">
    <copy todir="${deploy_testing.dir}">
        <fileset refid="app"/>
        <filterchain>
            <filterreader classname="preprocessor.PreProcessorFilter">
                <param name="TESTING" value="1" />
            </filterreader>
        </filterchain>
    </copy>
</target>

<target name="deploy_staging">
    <copy todir="${deploy_staging.dir}">
        <fileset refid="app"/>
        <filterchain>
            <filterreader classname="preprocessor.PreProcessorFilter">
                <param name="STAGING" value="1" />
            </filterreader>
        </filterchain>
    </copy>
</target>
```

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