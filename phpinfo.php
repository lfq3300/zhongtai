<?php
exec("mkdir demo");
echo exec("git pull origin develop", $result);
