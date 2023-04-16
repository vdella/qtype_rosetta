<?php

namespace classes;

$result = shell_exec('/usr/bin/python3 python/converter.py python/stub_fa.jff');
print $result;
