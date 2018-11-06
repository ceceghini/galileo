#!/bin/sh

cd /opt/galileo

# Creazione fatture da ordine
php yii odoo/invoice-from-order

# Verdestampa
php yii verdestampa/process-modelli
php yii verdestampa/process-product
php yii verdestampa/process-prezzi

# Tuttocartucce
php yii source-tuttocartucce/process-modelli
php yii source-tuttocartucce/process-product

# Puntorigenera
#php yii source-puntorigenera/process-url
#php yii source-puntorigenera/process-product
