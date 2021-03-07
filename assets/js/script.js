jQuery(document).ready(function () {

    jQuery('#store-historical').click(function (e) {
        e.preventDefault();
        historical();
    });

    validateData();
    jQuery('#weather_plugin_endpoint').on('keydown', validateData);
    jQuery('#weather_plugin_api').on('keydown', validateData);
    jQuery('#weather_plugin_api_key').on('keydown', validateData);
    jQuery('#weather_plugin_city_id').on('keydown', validateData);

    function validateData() {
        clearTimeout(this.timeValidate);
        this.timeValidate = setTimeout(function () {
            endpoint = jQuery('#weather_plugin_endpoint').val();
            api = jQuery('#weather_plugin_api').val();
            api_key = jQuery('#weather_plugin_api_key').val();
            city_id = jQuery('#weather_plugin_city_id').val();

            if (endpoint != '' && api != '' && api_key != '' && city_id != '') {
                jQuery('#store-historical').removeClass('hidden');
            } else {
                jQuery('#store-historical').addClass('hidden');
            }
        }, 200);
    }

    function historical() {
        circle = `<div class="circle-cont"><div class="circle"></div></div>`;
        jQuery('#form-settings').append(circle);
        api = jQuery('#weather_plugin_api').val();
        api_key = jQuery('#weather_plugin_api_key').val();
        endpoint = jQuery('#weather_plugin_endpoint').val();
        city_id = jQuery('#weather_plugin_city_id').val();
        const nonce = jQuery('#nonce').val();

        const data = {
            api: api,
            api_key: api_key,
            endpoint: endpoint,
            city_id: city_id,
            nonce: nonce
        }
        localStorage.setItem('data', JSON.stringify(data));

        fetch(`${data.api}?id=${data.city_id}&appid=${data.api_key}`)
            .then(data => data.json())
            .then((res) => {
                registerData(res, nonce);
            });

    }

    function registerData(res, nonce) {
        main = res.main;
        const data = {
            title: `${res.id}`,
            status: 'publish',
            content: 'b',
            temp: main.temp,
            city_id: res.id,
            name: res.name,
            temp_max: main.temp_max,
            temp_min: main.temp_min,
            pressure: main.pressure,
            humidity: main.humidity,
            main: res.weather[0].main,
            description: res.weather[0].description,
            _wpnonce: pluginData.nonce
        };

        fetch(`${pluginData.site_url}/wp-json/wp/v2/historical`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'accept': 'application/json',
                    'X-WP-Nonce': pluginData.nonce
                },
                body: JSON.stringify(data),
            })
            .then(data => data.json())
            .then((res) => {
                if (res) {
                    uploadHistorical(res.id, data);
                }
            })
    }

    function uploadHistorical(id, data) {
        data['id'] = id;

        fetch(`${pluginData.site_url}/wp-json/ht/v1/historical/`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'accept': 'application/json',
                    'X-WP-Nonce': pluginData.nonce
                },
                body: JSON.stringify(data),
            })
            .then(data => data.json())
            .then((res) => {
                console.log(res);
                if(res){
                    addMessage(true);
                } else{
                    addMessage(false);
                }
            })
    }

    function addMessage(data){
        jQuery('.circle-cont').remove();
        if(data == true){
            message = `<div class="message message-exitoso">
            ¡Datos añadidos correctamente!
            </div>`;
            jQuery('#form-settings').append(message);
        } else{
            message = `<div class="message message-error">
            ¡Ha habido un error al registrar la información!
            </div>`;
            jQuery('#form-settings').append(message);
        }
    }

    /* PAGINATION */

    jQuery('.arrow-right').click(function () {
        jQuery('.circle-cont').removeClass('hidden');
        paginationData('plus');
    });

    jQuery('.arrow-left').click(function () {
        jQuery('.circle-cont').removeClass('hidden');
        paginationData('less');
    });

    function paginationData(data) {
        if (data == 'plus') {
            pluginData.pagination = parseInt(pluginData.pagination) + 1;
            verifyErrores = 'siguientes';
        } else {
            pluginData.pagination = parseInt(pluginData.pagination) - 1;
            verifyErrores = 'anteriores';
        }

        fetch(`${pluginData.site_url}/wp-json/ht/v1/historicalGet?from=${pluginData.pagination}`)
            .then(data => data.json())
            .then((res) => {
                jQuery('.circle-cont').addClass('hidden');
                if (!res || pluginData.pagination < 1) {
                    jQuery('#errores-ajax').removeClass('hidden');
                    jQuery('#cst-error').text(verifyErrores);

                    if (verifyErrores == 'siguientes') {
                        pluginData.pagination = pluginData.pagination - 1
                    } else {
                        pluginData.pagination = pluginData.pagination + 1;
                    }
                } else {
                    jQuery('#errores-ajax').addClass('hidden');
                    console.log(res);
                    row = '';

                    for (i = 0; i < res.length; i++) {
                        row += `<div class="historical-item">
                <h2>${res[i].description}</h2>
                <p>Temp: ${res[i].temp}</p>
                <p>Temp min: ${res[i].temp_min}</p>
                <p>Temp max: ${res[i].temp_max}</p>
                <p>humidity: ${res[i].humidity}</p>
                </div>`;
                    }
                    jQuery('.historical-wrapper').html(row);
                }
            })
    }

});