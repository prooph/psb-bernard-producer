Bernard Message Producer for Prooph Service Bus
===============================================

[![Build Status](https://travis-ci.org/prooph/psb-bernard-producer.svg)](https://travis-ci.org/prooph/psb-bernard-producer)
[![Coverage Status](https://coveralls.io/repos/prooph/psb-bernard-producer/badge.svg?branch=master&service=github)](https://coveralls.io/github/prooph/psb-bernard-producer?branch=master)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/prooph/improoph)

Use [Bernard](https://github.com/bernardphp/bernard) as message producer for [Prooph Service Bus](https://github.com/prooph/service-bus).

## Important

This library will receive support until December 31, 2019 and will then be deprecated.

For further information see the official announcement here: [https://www.sasaprolic.com/2018/08/the-future-of-prooph-components.html](https://www.sasaprolic.com/2018/08/the-future-of-prooph-components.html)

# Installation

You can install the producer via composer by adding `"prooph/psb-bernard-producer": "^1.0"` as requirement to your composer.json.

Usage
-----

Check the [BernardMessageProducerTest](tests/BernardMessageProducerTest.php). Set up the producer is a straightforward task. Most of
the required components are provided by PSB and Bernard. This package only provides the glue code needed to let both
systems work together.

# Support

- Ask questions on Stack Overflow tagged with [#prooph](https://stackoverflow.com/questions/tagged/prooph).
- File issues at [https://github.com/prooph/psb-bernard-producer/issues](https://github.com/prooph/psb-bernard-producer/issues).
- Say hello in the [prooph gitter](https://gitter.im/prooph/improoph) chat.

# Contribute

Please feel free to fork and extend existing or add new features and send a pull request with your changes!
To establish a consistent code quality, please provide unit tests for all your changes and may adapt the documentation.

License
-------

Released under the [New BSD License](LICENSE).
