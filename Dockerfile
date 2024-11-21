FROM wordpress:6.7
ENV WOOCOMMERCE_VERSION 9.4.2
ENV WOOCOMMERCE_PDF_INVOICES_VERSION 3.7.2
ENV SEQUENCIAL_ORDER_NUMBERS_VERSION 1.5.6
ENV MONDU_PLUGIN_VERSION 3.0.3

RUN apt update
RUN apt -y install wget
RUN apt -y install unzip
RUN apt -y install zip
RUN apt -y install nano

# To avoid problems with another plugins
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install WP CLI
RUN wget https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -O /tmp/wp-cli.phar \
  && chmod +x /tmp/wp-cli.phar \
  && mv /tmp/wp-cli.phar /usr/local/bin/wp \
  && wp package install wp-cli/dist-archive-command --allow-root

# Copy source files to a temporary build directory
COPY . /temp-build/

# Compile plugin and move the zip to WordPress plugins directory
RUN cd /temp-build && \
    make zip && \
    unzip /temp-build/dist/mondu-trade-account.zip -d /usr/src/wordpress/wp-content/plugins/ && \
   	mv /usr/src/wordpress/wp-content/plugins/temp-build/ /usr/src/wordpress/wp-content/plugins/mondu-trade-account/ && \
    rm -rf /temp-build

# Install WooCommerce
RUN rm -rf /usr/src/wordpress/wp-content/plugins/woocommerce
RUN wget https://downloads.wordpress.org/plugin/woocommerce.${WOOCOMMERCE_VERSION}.zip -O /tmp/woocommerce.zip \
  && cd /usr/src/wordpress/wp-content/plugins \
  && unzip /tmp/woocommerce.zip \
  && rm /tmp/woocommerce.zip

# Install WooCommerce PDF Invoices
RUN rm -rf /usr/src/wordpress/wp-content/plugins/woocommerce-pdf-invoices-packing-slips
RUN wget https://downloads.wordpress.org/plugin/woocommerce-pdf-invoices-packing-slips.${WOOCOMMERCE_PDF_INVOICES_VERSION}.zip -O /tmp/woocommerce-pdf-invoices-packing-slips.zip \
  && cd /usr/src/wordpress/wp-content/plugins \
  && unzip /tmp/woocommerce-pdf-invoices-packing-slips.zip \
  && rm /tmp/woocommerce-pdf-invoices-packing-slips.zip

# Install Sequential Order Numbers
RUN rm -rf /usr/src/wordpress/wp-content/plugins/wt-woocommerce-sequential-order-numbers
RUN wget https://downloads.wordpress.org/plugin/wt-woocommerce-sequential-order-numbers.${SEQUENCIAL_ORDER_NUMBERS_VERSION}.zip -O /tmp/wt-woocommerce-sequential-order-numbers.zip \
  && cd /usr/src/wordpress/wp-content/plugins \
  && unzip /tmp/wt-woocommerce-sequential-order-numbers.zip \
  && rm /tmp/wt-woocommerce-sequential-order-numbers.zip

# Install Mondu Buy Now Pay Later
RUN rm -rf /usr/src/wordpress/wp-content/plugins/mondu-buy-now-pay-later
RUN wget https://github.com/mondu-ai/bnpl-checkout-woocommerce/releases/download/${MONDU_PLUGIN_VERSION}/mondu-buy-now-pay-later-${MONDU_PLUGIN_VERSION}.zip -O /tmp/mondu-buy-now-pay-later.zip \
  && cd /usr/src/wordpress/wp-content/plugins \
  && unzip /tmp/mondu-buy-now-pay-later.zip \
  && rm /tmp/mondu-buy-now-pay-later.zip

