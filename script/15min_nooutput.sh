#!/bin/sh

cd /opt/galileo

# Import fatture fornitore
php yii odoo/reduce-account-invoice > /dev/null 2>&1
