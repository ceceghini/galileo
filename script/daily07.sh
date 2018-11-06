#!/bin/sh

cd /opt/galileo

# Elaborazione prodotti da supplies24
php yii source-supplies24/process-main

# Join dei prodotti
php /opt/galileo/yii toner/join

# Elaborazione prodotti
php yii toner/process-product

# Elaborazione modelli
php yii toner/process-modelli

# Normalizzazione dei dati
php yii odoo/normalize
