#!/bin/sh

cd /opt/galileo

# Import fatture fornitore
php yii odoo/import-account-invoice

# Import estratti conto
php /opt/galileo/yii odoo/import-bank-crgiovo
php /opt/galileo/yii odoo/import-bank-payplug
php /opt/galileo/yii odoo/import-bank-paypal
php /opt/galileo/yii odoo/import-bank-cartasi
php /opt/galileo/yii odoo/import-bank-sda

# Riconciliazione
php yii odoo/reconcilie
