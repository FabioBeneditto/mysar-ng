#!/bin/bash

### 01 00 * * * root /srv/www/htdocs/mysar/bin/mysar-rotate-diario.sh


LOGDIR="/var/log/squid"
DATE=$(date +'%Y-%m-%d')
ONTEM=$(date --date "1 day ago" +%d-%m-%Y) 
ONTEM_SARG=$(date --date "1 day ago" +%d/%m/%Y) 
DATE_MES=$(date +'%Y-%m')
MENSALDIR="${LOGDIR}/${DATE_MES}"


if [ ! -d ${MENSALDIR} ]; then
        mkdir -p ${MENSALDIR}
fi

# Parando squid...
/etc/init.d/squid stop

# Armazena log atual
mv ${LOGDIR}/access.log ${MENSALDIR}/access.log-${ONTEM}

# cria novo arquivo de log
touch ${LOGDIR}/access.log
chown squid.www ${LOGDIR}/access.log

# inicia o squid
/etc/init.d/squid start

gzip ${MENSALDIR}/access.log-${ONTEM}

mysar --offline --logfile ${LOGDIR}/access.log

# fim
