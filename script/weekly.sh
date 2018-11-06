#!/bin/sh

cd /opt/galileo

# Elaborazione verdestampa
php yii verdestampa/process-brand
php yii verdestampa/process-serie
#php yii verdestampa/reset-weekly
#php yii verdestampa/download-photo

# Elaborazione tuttocartucce
php yii source-tuttocartucce/reset-weekly
php yii source-tuttocartucce/process-brand
php yii source-tuttocartucce/process-serie

# Caricamento vendite da odoo
php /opt/galileo/yii odoo/process-sale
