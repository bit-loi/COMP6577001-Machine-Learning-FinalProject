#!/bin/sh
set -e

a2dismod -q mpm_event mpm_worker || true
a2enmod -q mpm_prefork rewrite || true
a2enconf -q servername || true

exec apache2-foreground
