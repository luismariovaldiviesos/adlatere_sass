<script>

    document.addEventListener('livewire:load', function () {

        //-------------------------------------------------------------------------------------//
        //                        TOP 5 PRODUCTS
        // ------------------------------------------------------------------------------------//
        var optionsTop5 = {
            //   AQUI SE PONE LA DATA QUE VIENE DESDE EL BACK
            series: [
            parseFloat(@this.top5Data[0]['total']),
            parseFloat(@this.top5Data[1]['total']),
            parseFloat(@this.top5Data[2]['total']),
            parseFloat(@this.top5Data[3]['total']),
            parseFloat(@this.top5Data[4]['total'])
          ],
          chart: {
          type: 'donut',
          height:392,
        },
        labels:[
            @this.top5Data[0]['product'],
            @this.top5Data[1]['product'],
            @this.top5Data[2]['product'],
            @this.top5Data[3]['product'],
            @this.top5Data[4]['product']
        ],
        responsive: [{
          breakpoint: 480,
          options: {
            chart: {
              width: 200
            },
            legend: {
              position: 'bottom'
            }
          }
        }]
        };

        var chartTop5 = new ApexCharts(document.querySelector("#chartTop5"), optionsTop5);
        chartTop5.render();





        /// graficoventas de la semana


        var optionsWeek = {
          series: [{
          name: 'Ventas del día',
          data: [
            parseFloat(@this.weekSales_Data[0]),
            parseFloat(@this.weekSales_Data[1]),
            parseFloat(@this.weekSales_Data[2]),
            parseFloat(@this.weekSales_Data[3]),
            parseFloat(@this.weekSales_Data[4]),
            parseFloat(@this.weekSales_Data[5]),
            parseFloat(@this.weekSales_Data[6])
          ]
        }],
          chart: {
          height: 380,
          type: 'area'
        },
        dataLabels: {
          enabled: true,
          formatter: function(val){
            return '$' + parseFloat(val).toFixed(2);
          }
        },
        stroke: {
          curve: 'smooth'
        },
        xaxis: {
          categories: ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"]
        },
        tooltip: {
          x: {
            format: 'dd/MM/yy HH:mm'
          },
        },
        };

        var chartWeek = new ApexCharts(document.querySelector("#chartArea"), optionsWeek);
        chartWeek.render();



        //  GRAFICO VENTAS ANUALES POR MES
        var optionsMonth = {
          series: [{
          name: 'Ventas del año mensuales',
          data: @this.salesByMonth_Data
        }],
          chart: {
          height: 350,
          type: 'bar',
        },
        plotOptions: {
          bar: {
            borderRadius: 10,
            dataLabels: {
              position: 'top', // top, center, bottom
            },
          }
        },
        dataLabels: {
          enabled: true,
          formatter: function (val) {
            return "$" + parseFloat(val).toFixed(2) ;
          },
          offsetY: -20,
          style: {
            fontSize: '12px',
            colors: ["#304758"]
          }
        },

        xaxis: {
          categories: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
          position: 'top',
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false
          },
          crosshairs: {
            fill: {
              type: 'gradient',
              gradient: {
                colorFrom: '#D8E3F0',
                colorTo: '#BED1E6',
                stops: [0, 100],
                opacityFrom: 0.4,
                opacityTo: 0.5,
              }
            }
          },
          tooltip: {
            enabled: true,
          }
        },
        yaxis: {
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false,
          },
          labels: {
            show: false,
            formatter: function (val) {
              return "$" + parseFloat(val).toFixed(2) ;
            }
          }

        },
        title: {
          text: totalYearSales(),
          floating: true,
          offsetY: 330,
          align: 'center',
          style: {
            color: '#444'
          }
        }
        };

        var chartMonth = new ApexCharts(document.querySelector("#chartMonth"), optionsMonth);
        chartMonth.render();


        function totalYearSales() {
            var total = 0
            @this.salesByMonth_Data.forEach(item => {
                total += parseFloat(item)
            });

            return 'Total: $' + total.toFixed(2)
        }

        // GRAFICO VENTAS POR FORMA DE PAGO
        var paymentData = @json($salesByPaymentMethod_Data);
        
        var optionsPaymentMethod = {
            series: paymentData.map(item => parseFloat(item.total)),
            chart: {
                height: 350,
                width: '100%',
                type: 'pie',
            },
            labels: paymentData.map(item => item.method),
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        var chartPaymentMethod = new ApexCharts(document.querySelector("#chartPaymentMethod"), optionsPaymentMethod);
        chartPaymentMethod.render();


        function totalYearSales() {
            var total = 0
            @this.salesByMonth_Data.forEach(item => {
                total += parseFloat(item)
            });

            return 'Total: $' + total.toFixed(2)
        }


        // ── JUICIOS POR MES (barra) ──
        var optionsJuiciosMonth = {
          series: [{ name: 'Juicios', data: @this.juiciosByMonth_Data }],
          chart: { height: 350, type: 'bar' },
          plotOptions: { bar: { borderRadius: 10, dataLabels: { position: 'top' } } },
          dataLabels: { enabled: true, offsetY: -20, style: { fontSize: '12px', colors: ["#304758"] } },
          xaxis: { categories: ["Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"], position: 'top' },
          yaxis: { labels: { formatter: function(val){ return parseInt(val); } } }
        };
        var chartJuiciosMonth = new ApexCharts(document.querySelector("#chartJuiciosMonth"), optionsJuiciosMonth);
        chartJuiciosMonth.render();

        // ── TOP MATERIAS (donut) ──
        var optionsJuiciosMateria = {
          series: [
            parseFloat(@this.juiciosByMateria_Data[0]['total']),
            parseFloat(@this.juiciosByMateria_Data[1]['total']),
            parseFloat(@this.juiciosByMateria_Data[2]['total']),
            parseFloat(@this.juiciosByMateria_Data[3]['total']),
            parseFloat(@this.juiciosByMateria_Data[4]['total'])
          ],
          chart: { type: 'donut', height: 392 },
          labels: [
            @this.juiciosByMateria_Data[0]['materia'],
            @this.juiciosByMateria_Data[1]['materia'],
            @this.juiciosByMateria_Data[2]['materia'],
            @this.juiciosByMateria_Data[3]['materia'],
            @this.juiciosByMateria_Data[4]['materia']
          ]
        };
        var chartJuiciosMateria = new ApexCharts(document.querySelector("#chartJuiciosMateria"), optionsJuiciosMateria);
        chartJuiciosMateria.render();

        // ── POR ESTADO PROCESAL (barras horizontales) ──
        var juiciosEstadoData = @json($juiciosByEstado_Data);
        var optionsJuiciosEstado = {
          series: [{ name: 'Juicios', data: juiciosEstadoData.map(item => parseInt(item.total)) }],
          chart: { height: 350, type: 'bar' },
          plotOptions: { bar: { borderRadius: 6, horizontal: true } },
          dataLabels: { enabled: true },
          xaxis: { categories: juiciosEstadoData.map(item => item.estado) }
        };
        var chartJuiciosEstado = new ApexCharts(document.querySelector("#chartJuiciosEstado"), optionsJuiciosEstado);
        chartJuiciosEstado.render();

        // ── AUDIENCIAS 14 DÍAS (área) ──
        var optionsAudiencias14 = {
          series: [{ name: 'Audiencias', data: @this.audiencias14_Data }],
          chart: { height: 350, type: 'area' },
          dataLabels: { enabled: false },
          stroke: { curve: 'smooth' },
          xaxis: { categories: @json($audiencias14_Labels) }
        };
        var chartAudiencias14 = new ApexCharts(document.querySelector("#chartAudiencias14"), optionsAudiencias14);
        chartAudiencias14.render();


        //reload charts info
        window.addEventListener('reload-scripts', event => {
            // actualizar grafico semanal
            chartWeek.updateSeries([{
                data: @this.weekSales_Data
            }])

            //actualixar grafico mensual
            chartMonth.updateSeries([{
                data: @this.salesByMonth_Data
            }])

            // actualixar grafico top 5
            var newData = [
                parseFloat(@this.top5Data[0]['total']),
                parseFloat(@this.top5Data[1]['total']),
                parseFloat(@this.top5Data[2]['total']),
                parseFloat(@this.top5Data[3]['total']),
                parseFloat(@this.top5Data[4]['total'])

            ]
            chartTop5.updateSeries(newData)

            // Actualizar el título del total anual
            chartMonth.updateOptions({
                title: {
                    text: totalYearSales()
                }
            })

            // Actualizar grafico formas de pago
            // Para el reload usamos @this ya que la data cambio en el backend
            // O podemos re-leer @this.salesByPaymentMethod_Data
            
            var newPaymentData = @this.salesByPaymentMethod_Data;
            chartPaymentMethod.updateOptions({
                series: newPaymentData.map(item => parseFloat(item.total)),
                labels: newPaymentData.map(item => item.method)
            })

            // Actualizar gráficos de juicios
            chartJuiciosMonth.updateSeries([{ data: @this.juiciosByMonth_Data }]);
            chartJuiciosMateria.updateSeries([
                parseFloat(@this.juiciosByMateria_Data[0]['total']),
                parseFloat(@this.juiciosByMateria_Data[1]['total']),
                parseFloat(@this.juiciosByMateria_Data[2]['total']),
                parseFloat(@this.juiciosByMateria_Data[3]['total']),
                parseFloat(@this.juiciosByMateria_Data[4]['total'])
            ]);
            var newEstadoData = @this.juiciosByEstado_Data;
            chartJuiciosEstado.updateOptions({
                series: [{ data: newEstadoData.map(item => parseInt(item.total)) }],
                xaxis: { categories: newEstadoData.map(item => item.estado) }
            });
            chartAudiencias14.updateSeries([{ data: @this.audiencias14_Data }]);
            chartAudiencias14.updateOptions({ xaxis: { categories: @this.audiencias14_Labels } });

        })


    })






</script>
