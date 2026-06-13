#!/bin/sh
set -e

a2dismod -q mpm_event mpm_worker || true
a2enmod -q mpm_prefork rewrite || true
a2enconf -q servername || true

php /var/www/html/scripts/railway-bootstrap-db.php || echo "[shopmart] DB bootstrap failed; continuing to start Apache."

exec apache2-foreground
