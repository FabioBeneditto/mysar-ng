#!/bin/bash

#* * * * *       root    /var/www/html/mysar/mysar-cron.sh

###############################################################################
# Testa se script já encontra-se em execução

test_running() {
        RUNNING=$(ps aux | grep "$(printf '%s\n' "${0##*/}")" | grep bash | grep -v "bash -c" | grep -v $$ | grep -v grep | wc -l)
        if [ ${RUNNING} -gt 1 ]; then
                echo "Script ja esta rodando, aguardando 10 segundos para tentar novamente."
                sleep 10
                RUNNING=$(ps aux | grep "$(printf '%s\n' "${0##*/}")" | grep bash | grep -v "bash -c" | grep -v $$ | grep -v grep | wc -l)
                if [ ${RUNNING} -gt 1 ]; then
                        echo "Script ja esta rodando, nao sera executado novamente."
                        exit 1
                fi
        fi
}

test_running
###############################################################################
# Iniciando script

#/usr/bin/mysar > /var/www/html/mysar-reports/log/mysar-importer.log 2>&1
/usr/bin/mysar --offline --logfile /var/log/squid/access.log > /var/www/html/mysar/log/mysar-importer.log 2>&1


###############################################################################
# Fim do script
