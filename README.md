Bernard message dispatcher for ProophServiceBus
==================================================


Use [Bernard](http://bernardphp.com/en/latest/) as message dispatcher for [ProophServiceBus](https://github.com/prooph/service-bus).

# Installation

You can install the dispatcher via composer by adding `"prooph/psb-bernard-dispatcher": "~0.1"` as requirement to your composer.json.

Usage
-----

Check the [BernardMessageDispatcherTest](tests/BernardMessageDispatcherTest.php). Set up the dispatcher is a straightforward task. Most of
the required components are provided by PSB and Bernard. This package only provides the glue code needed to let both
systems work together.

# Support

- Ask questions on [prooph-users](https://groups.google.com/forum/?hl=de#!forum/prooph) google group.
- File issues at [https://github.com/prooph/psb-bernard-dispatcher/issues](https://github.com/prooph/psb-bernard-dispatcher/issues).

# Contribute

Please feel free to fork and extend existing or add new features and send a pull request with your changes!
To establish a consistent code quality, please provide unit tests for all your changes and may adapt the documentation.

License
-------

Released under the [New BSD License](https://github.com/prooph/psb-bernard-dispatcher/blob/master/LICENSE).
