<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">

    <div id="map" x-load-css="[
            @js(\Filament\Support\Facades\FilamentAsset::getStyleHref('leaflet-stylesheet')),
            @js(\Filament\Support\Facades\FilamentAsset::getStyleHref('leaflet-draw-plugin-stylesheet')),
            @js(\Filament\Support\Facades\FilamentAsset::getStyleHref('leaflet-search-plugin-stylesheet'))
        ]" x-load-js="[
            @js(\Filament\Support\Facades\FilamentAsset::getScriptSrc('leaflet-script')),
            @js(\Filament\Support\Facades\FilamentAsset::getScriptSrc('leaflet-draw-plugin-script')),
            @js(\Filament\Support\Facades\FilamentAsset::getScriptSrc('leaflet-search-plugin-script'))
        ]" wire:ignore x-init="initMap($el, $data)" x-data="{
            map: null,
            drawControl: null,
            drawnItems: null,
            state: $wire.$entangle(@js($getStatePath())),
        }">
    </div>

</x-dynamic-component>

<style>
    .leaflet-container {
        height: 400px;
        width: 100%;
        background: #fff !important;
        color: #333 !important;
        direction: ltr !important;
        text-align: left !important;
    }

    .leaflet-control-pinsearch {
        position: relative;
        direction: ltr;
    }

    .search-input-container {
        position: relative;
        display: flex;
        align-items: center;
        flex-direction: row-reverse;
    }

    .search-input {
        width: 200px;
        height: 30px;
        padding: 5px 10px 5px 30px;
        background-color: #fff !important;
        color: #333 !important;
        border: 1px solid #ccc;
    }

    .search-icon {
        position: absolute;
        left: 10px;/ top: 50%;
        transform: translateY(-50%);
        font-size: 1.5em;
        pointer-events: none;
        color: #333;
    }

    .search-results {
        display: none;
        position: absolute;
        top: calc(100% + 5px);
        left: 0;
        right: 0;
        padding: 5px;
        background-color: #fff !important;
        border: 1px solid #ccc;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        z-index: 9999;
        direction: ltr;
    }

    .search-results-item {
        padding: 5px;
        cursor: pointer;
        background-color: #fff;
    }

    .search-results-item:hover,
    .search-results-item.highlight {
        background-color: #f0f0f0;
    }
</style>

<script>
    function initMap(el, data) {
        // detect current locale from Laravel
        const locale = '{{ App::getLocale() }}';

        // initialize map
        data.map = L.map(el, {
            zoomControl: false,
        }).setView([23.8859, 45.0792], 6);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: locale === 'ar'
                ? '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> المساهمون'
                : '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(data.map);

        // 🔹 Translate draw controls
        if (locale === 'ar') {
            L.drawLocal = {
                draw: {
                    toolbar: {
                        actions: {
                            title: "إلغاء الرسم",
                            text: "إلغاء"
                        },
                        finish: {
                            title: "إنهاء الرسم",
                            text: "إنهاء"
                        },
                        undo: {
                            title: "حذف آخر نقطة مرسومة",
                            text: "تراجع"
                        },
                        buttons: {
                            polyline: "ارسم خطًا",
                            polygon: "ارسم مضلعًا",
                            rectangle: "ارسم مستطيلاً",
                            circle: "ارسم دائرة",
                            marker: "ضع علامة",
                            circlemarker: "ضع دائرة صغيرة"
                        }
                    },
                    handlers: {
                        circle: {
                            tooltip: {
                                start: "انقر واسحب لرسم دائرة."
                            },
                            radius: "نصف القطر"
                        },
                        circlemarker: {
                            tooltip: {
                                start: "انقر على الخريطة لوضع دائرة صغيرة."
                            }
                        },
                        marker: {
                            tooltip: {
                                start: "انقر على الخريطة لوضع علامة."
                            }
                        },
                        polygon: {
                            tooltip: {
                                start: "انقر لبدء رسم الشكل.",
                                cont: "انقر للمتابعة في رسم الشكل.",
                                end: "انقر على النقطة الأولى لإغلاق الشكل."
                            }
                        },
                        polyline: {
                            error: "<strong>خطأ:</strong> لا يمكن أن تتقاطع حواف الشكل!",
                            tooltip: {
                                start: "انقر لبدء رسم الخط.",
                                cont: "انقر للمتابعة في رسم الخط.",
                                end: "انقر على آخر نقطة لإنهاء الخط."
                            }
                        },
                        rectangle: {
                            tooltip: {
                                start: "انقر واسحب لرسم مستطيل."
                            }
                        },
                        simpleshape: {
                            tooltip: {
                                end: "حرر زر الفأرة لإنهاء الرسم."
                            }
                        }
                    }
                },
                edit: {
                    toolbar: {
                        actions: {
                            save: {
                                title: "حفظ التغييرات",
                                text: "حفظ"
                            },
                            cancel: {
                                title: "إلغاء التعديلات والتراجع عن كل التغييرات",
                                text: "إلغاء"
                            },
                            clearAll: {
                                title: "مسح جميع الطبقات",
                                text: "مسح الكل"
                            }
                        },
                        buttons: {
                            edit: "تعديل الطبقات",
                            editDisabled: "لا توجد طبقات للتعديل",
                            remove: "حذف الطبقات",
                            removeDisabled: "لا توجد طبقات للحذف"
                        }
                    },
                    handlers: {
                        edit: {
                            tooltip: {
                                text: "اسحب المقابض أو العلامات لتعديل العناصر.",
                                subtext: "انقر على إلغاء للتراجع عن التغييرات."
                            }
                        },
                        remove: {
                            tooltip: {
                                text: "انقر على عنصر لإزالته."
                            }
                        }
                    }
                }
            };

            L.control.zoom({
                zoomInTitle: 'تكبير الخريطة',
                zoomOutTitle: 'تصغير الخريطة',
            }).addTo(data.map);
        } else {
            L.control.zoom().addTo(data.map);
        }

        // initialize draw plugin
        data.drawnItems = new L.FeatureGroup().addTo(data.map);
        data.drawControl = new L.Control.Draw({
            draw: {
                polygon: false,
                polyline: false,
                rectangle: true,
                circle: false,
                marker: false,
                circlemarker: false
            },
            edit: {
                featureGroup: data.drawnItems,
                edit: data.state ? false : true,
                remove: data.state ? false : true
            }
        });
        data.map.addControl(data.drawControl);

        // initialize search plugin
        const saudiCities = [
            { en: 'Riyadh', ar: 'الرياض', lat: 24.633, lng: 46.717 },
            { en: 'Jeddah', ar: 'جدة', lat: 21.4925, lng: 39.17757 },
            { en: 'Mecca', ar: 'مكة المكرمة', lat: 21.4225, lng: 39.8233 },
            { en: 'Medina', ar: 'المدينة المنورة', lat: 24.4700, lng: 39.6100 },
            { en: 'Dammam', ar: 'الدمام', lat: 26.43442, lng: 50.10326 },
            { en: 'Ta’if', ar: 'الطائف', lat: 21.27028, lng: 40.41583 },
            { en: 'Tabuk', ar: 'تبوك', lat: 28.3998, lng: 36.57151 },
            { en: 'Ha’il', ar: 'حائل', lat: 27.52188, lng: 41.69073 },
            { en: 'Najran', ar: 'نجران', lat: 17.49326, lng: 44.12766 },
            { en: 'Abha', ar: 'أبها', lat: 18.21639, lng: 42.50528 },
            { en: 'Al Khobar', ar: 'الخبر', lat: 26.2389, lng: 50.1971 },
            { en: 'Yanbu', ar: 'ينبع', lat: 24.1868, lng: 38.0264 },
            { en: 'Al Jubayl', ar: 'الجبيل', lat: 27.0333, lng: 49.6667 },
            { en: 'Al Qatif', ar: 'القطيف', lat: 26.5652, lng: 49.9964 },
            { en: 'Safwa', ar: 'صفوى', lat: 26.5014, lng: 50.0108 },
            { en: 'Sayhat', ar: 'سيهات', lat: 26.5001, lng: 50.0118 },
            { en: 'Al Hufuf', ar: 'الهفوف', lat: 25.3881, lng: 49.5881 },
            { en: 'Arar', ar: 'عرعر', lat: 30.9833, lng: 41.0167 },
            { en: 'Al Mubarraz', ar: 'المبرز', lat: 25.4077, lng: 49.5903 },
            { en: 'Khamis Mushait', ar: 'خميس مشيط', lat: 18.3294, lng: 42.7594 },
            { en: 'Jazan', ar: 'جازان', lat: 16.9097, lng: 42.5679 },
            { en: 'Buraidah', ar: 'بريدة', lat: 26.32599, lng: 43.97497 },
            { en: 'Al Kharj', ar: 'الخرج', lat: 24.1554, lng: 47.3346 },
            { en: 'Dhahran', ar: 'الظهران', lat: 26.2364, lng: 50.0326 },
            { en: 'Hafar Al-Batin', ar: 'حفر الباطن', lat: 28.4469, lng: 45.9489 },
            { en: 'Unayzah', ar: 'عنيزة', lat: 26.0941, lng: 43.9735 },
            { en: 'Al-Muzahmiyya', ar: 'المزاحمية', lat: 24.4649, lng: 46.2739 },
            { en: 'Al Bahah', ar: 'الباحة', lat: 20.015, lng: 41.467 }
        ];
        saudiCities.forEach(city => {
            L.marker([city.lat, city.lng], { title: locale === 'ar' ? city.ar : city.en }).addTo(data.map);
        });

        var searchBar = L.control.pinSearch({
            position: 'topright',
            placeholder: locale === 'ar' ? 'ابحث...' : 'Search...',
            buttonText: locale === 'ar' ? 'بحث' : 'Search',
            onSearch: function (query) {
                //
            },
            searchBarWidth: '200px',
            searchBarHeight: '30px',
            maxSearchResults: 2
        }).addTo(data.map);

        // handle draw events and update state
        const updateState = () => {
            const rectangles = [];
            data.drawnItems.eachLayer(layer => {
                if (layer.getBounds) {
                    const bounds = layer.getBounds();
                    const sw = bounds.getSouthWest();
                    const ne = bounds.getNorthEast();
                    const coords = [
                        { name: 'bl', latitude: sw.lat, longitude: sw.lng },
                        { name: 'tl', latitude: ne.lat, longitude: sw.lng },
                        { name: 'tr', latitude: ne.lat, longitude: ne.lng },
                        { name: 'br', latitude: sw.lat, longitude: ne.lng },
                    ];
                    const center = bounds.getCenter();
                    coords.push({ name: 'c', latitude: center.lat, longitude: center.lng });
                    rectangles.push(coords);
                }
            });
            data.state = rectangles[0];
        };

        // Event listeners
        data.map.on('draw:created', (e) => {
            if (data.drawnItems.getLayers().length > 0) {
                data.drawnItems.clearLayers();
            }
            data.drawnItems.addLayer(e.layer);
            updateState();
        });
        data.map.on('draw:edited', updateState);
        data.map.on('draw:deleted', updateState);

        // Load existing state
        if (data.state && Array.isArray(data.state) && data.state.length > 0) {
            const rectCoords = data.state
                .filter(coord => coord.name !== 'c')
                .map(coord => [coord.latitude, coord.longitude]);
            const polygon = L.polygon(rectCoords, { color: 'blue' });
            data.drawnItems.addLayer(polygon);
            data.map.fitBounds(polygon.getBounds());
        }

        // Invalidate map size to ensure proper rendering
        setTimeout(() => data.map.invalidateSize(), 0);
    }
</script>