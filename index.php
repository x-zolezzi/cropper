<?php
if (!empty($_POST)) {
    var_dump($_POST);
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta charset="utf-8" />
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.2/basic.css" integrity="sha512-Ucip2staDcls3OuwEeh5s9rRVYBsCA4HRr18+qd0Iz3nYpnfUeCIMh/82aHKeYgdaXGebmi9vcREw7YePXsutQ==" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-minicolors/2.3.5/jquery.minicolors.css" integrity="sha512-KeEVSm+Vk+xYRPc7EQnXb4mUsNykSh6WFGZUB/UqerCrRc1kuIjEbcsK8LMZGfOnVQuWRI8Bm1dgFvcSqVmhZw==" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/round-slider@1.6.1/dist/roundslider.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/cropperjs/dist/cropper.css" />

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.2/min/dropzone.min.js" integrity="sha512-9WciDs0XP20sojTJ9E7mChDXy6pcO0qHpwbEJID1YVavz2H6QBz5eLoDD8lseZOb2yGT8xDNIV7HIe1ZbuiDWg==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-minicolors/2.3.5/jquery.minicolors.min.js" integrity="sha512-FVnzYpPeG7mAH2iLD3T+pXpsBTUwF0Ea9C7sL85QLzF/GVDMDStSLUYiWl1Vuz5pe69LJCy7pFTtSEEIhVj/FQ==" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/round-slider@1.6.1/dist/roundslider.min.js"></script>
    <script src="https://unpkg.com/cropperjs@1.5.5/dist/cropper.js"></script>
    <script type="text/javascript">
        var width_final = 500;
        var height_final = 500;

        var cropper;
        var editor;
        var cropper_is_ready = false;
        var slider_opactity_image;
        var slider_opactity_overlay;
        $(function() {

            $('#file_exist').on('click', function() {
                cropImage('/5598.jpg');
            })

            $("#zone-upload").dropzone({
                url: '/upload-file',
                maxFilesize: <?= (int) (ini_get('upload_max_filesize') <= ini_get('post_max_size') ? ini_get('upload_max_filesize') : ini_get('post_max_size')) ?>,
                addRemoveLinks: true,
                acceptedFiles: 'image/jpg, image/jpeg, image/png',
                previewsContainer: "#zone-upload .dropzone-previews",
                transformFile: function(file, done) {
                    var myDropZone = this;
                    cropImage(file);
                },
                sending: function(file, xhr, formData) {
                    debugger;
                },
                success: function(file, xhr, formData) {
                    if (xhr.success) {
                        var $el = $('<input />');
                        $el.attr('type', 'text');
                        $el.attr('name', 'photos[]');
                        $el.val(xhr.filename);
                        $('#files').append($el);
                    }
                }
            }).addClass('dropzone');

            $(window).on('resize', function() {
                setTimeout(calcul_taille_cropbox, 200);
            });
        });

        function cropImage(file) {
            editor = document.createElement('div');
            editor.style.position = 'fixed';
            editor.style.left = 0;
            editor.style.right = 0;
            editor.style.top = 0;
            editor.style.bottom = 0;
            editor.style.zIndex = 9999;
            editor.style.backgroundColor = '#000';

            var image = new Image();

            //Dropzone OU fichier existant
            if (typeof file == 'object') {
                image.src = URL.createObjectURL(file);
            } else {
                image.src = file;
            }
            image.onload = function() {
                var image_trop_petite = false;
                if (this.width < width_final) {
                    image_trop_petite = true;
                }
                if (this.height < height_final) {
                    image_trop_petite = true;
                }
                if (image_trop_petite) {
                    $(editor).prepend('<p id="taille-image-petite" style="color: #dc3545; text-align: center; z-index: 9999;">Votre image est trop petite.</p>');
                }
            }

            editor.appendChild(image);
            document.body.appendChild(editor);

            // Bouton d'action
            var $el = $('<button class="btn btn-success" id="btn_crop_valider" style="position: absolute; left: 10px; top: 10px; z-index: 9999;">Valider</button>').on('click', function() {

                // $('#file').val(cropper.originalUrl); //@TODO : Trouver solution pour envoyer l'image source

                $('#cropdata').val(JSON.stringify({
                    'data': cropper.getData(),
                    'canvas': cropper.getCanvasData(),
                    'cropbox': cropper.getCropBoxData(),
                    'image': cropper.getImageData,
                    'container': cropper.getContainerData(),
                    'overlay': ($('#couleur-overlay').val() != '' ? $('#couleur-overlay').minicolors('rgbaString') : false),
                    'background': $('#couleur-background').val()
                }));

                var canvas = cropper.getCroppedCanvas({
                    width: width_final,
                    height: height_final
                });

                canvas.toBlob(function(blob) {
                    var id_image = 'image-' + Date.now();
                    $('.dropzone-previews').append('<div id="' + id_image + '" style="position: relative; display: inline-block;"><img src="' + URL.createObjectURL(blob) + '" class="img-responsive" /></div>');

                    $('#' + id_image).on('click', function() {
                        alert('edit image');
                    })

                    //@TODO : Appliqué  opacité
                    if ($('#style_' + id_image).length == 0) {
                        $('head').append('<style id="style_' + id_image + '" type="text/css"></style>');
                    }
                    $('#style_' + id_image).html('#' + id_image + ' { background-color: ' + $('#couleur-background').val() + '; } #' + id_image + ':after { content: ""; position:absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: ' + ($('#couleur-overlay').val() != '' ? $('#couleur-overlay').minicolors('rgbaString') : 'transparent') + '; }</style>');

                    // myDropZone.createThumbnail(
                    //     blob,
                    //     myDropZone.options.thumbnailWidth,
                    //     myDropZone.options.thumbnailHeight,
                    //     myDropZone.options.thumbnailMethod,
                    //     false,
                    //     function(dataURL) {
                    //         myDropZone.emit('thumbnail', file, dataURL);
                    //         done(blob);
                    //     }
                    // );


                    $(editor).remove();
                });
            });
            $(editor).append($el);
            var $el = $('<button class="btn btn-danger" id="btn_crop_annuler" style="position: absolute; left: 10px; top: 40px; z-index: 9999;">Annuler</button>').on('click', function() {
                $(editor).remove();
            });
            $(editor).append($el);

            // Bouton Zoom
            var $el = $('<button class="btn btn-primary" id="btn_zoom_plus" style="position: absolute; right: 60px; top: 10px; z-index: 9999;">++</button>').on('click', function() {
                cropper.zoom(0.1);
            });
            $(editor).append($el);
            var $el = $('<button class="btn btn-primary" id="btn_zoom_moins" style="position: absolute; right: 60px; top: 40px; z-index: 9999;">--</button>').on('click', function() {
                cropper.zoom(-0.1);
            });
            $(editor).append($el);
            var $el = $('<button class="btn btn-primary" id="btn_zoom_plus" style="position: absolute; right: 10px; top: 10px; z-index: 9999;">+</button>').on('click', function() {
                cropper.zoom(0.01);
            });
            $(editor).append($el);
            var $el = $('<button class="btn btn-primary" id="btn_zoom_moins" style="position: absolute; right: 10px; top: 40px; z-index: 9999;">-</button>').on('click', function() {
                cropper.zoom(-0.01);
            });
            $(editor).append($el);

            // Bouton Déplacement
            var $el = $('<button class="btn btn-primary" style="position: absolute; right: 10px; top: 70px; z-index: 9999;">←</button>').on('click', function() {
                cropper.move(-10, 0);
            });
            $(editor).append($el);
            var $el = $('<button class="btn btn-primary" style="position: absolute; right: 10px; top: 100px; z-index: 9999;">→</button>').on('click', function() {
                cropper.move(10, 0);
            });
            $(editor).append($el);
            var $el = $('<button class="btn btn-primary" style="position: absolute; right: 10px; top: 130px; z-index: 9999;">↑</button>').on('click', function() {
                cropper.move(0, -10);
            });
            $(editor).append($el);
            var $el = $('<button class="btn btn-primary" style="position: absolute; right: 10px; top: 160px; z-index: 9999;">↓</button>').on('click', function() {
                cropper.move(0, 10);
            });
            $(editor).append($el);

            //Symétrie
            var $el = $('<button class="btn btn-primary" style="position: absolute; right: 70px; top: 70px; z-index: 9999;">_</button>').on('click', function() {
                cropper.scaleY(cropper.getImageData().scaleY * -1);
            });
            $(editor).append($el);
            var $el = $('<button class="btn btn-primary" style="position: absolute; right: 70px; top: 110px; z-index: 9999;">|</button>').on('click', function() {
                cropper.scaleX(cropper.getImageData().scaleX * -1);
            });
            $(editor).append($el);

            // Opacité image
            $(editor).append('<p style="position: absolute; right: 10px; top: 200px; z-index: 9999; color: #fff;">Opacité de l\'image</p>');
            slider_opactity_image = $('<div id="slider-opacity-image" style="position: absolute; right: 10px; top: 230px; z-index: 9999; width: 100px">').slider({
                min: 0,
                max: 100,
                value: 100,
                slide: function(event, ui) {
                    $("#input-opacity-image").val(ui.value);
                    change_opacite_image(ui.value / 100);
                }
            });
            $(editor).append(slider_opactity_image);

            var $el = $('<input id="input-opacity-image" type="number" value="100" min="0" max="100" step="1" style="position: absolute; right: 10px; top: 260px; z-index: 9999;">').on('keyup', function() {
                slider_opactity_image.slider('value', $(this).val());
                change_opacite_image($(this).val() / 100);
            });
            $(editor).append($el);

            // Overlay
            $(editor).append('<p style="position: absolute; right: 10px; top: 340px; z-index: 9999; color: #fff;">Overlay</p>');
            //@TODO : COLORPICKER OVERLAY
            var $el = $('<div style="position: absolute; right: 10px; top: 370px; z-index: 99999;"><input type="text" data-opacity="0.5" id="couleur-overlay"></div>')
            $(editor).append($el);
            $('#couleur-overlay').minicolors({
                opacity: true,
                position: 'bottom right',
                change: function(value, opacity) {
                    change_overlay($('#couleur-overlay').minicolors('rgbaString'));
                }
            });

            // slider_opactity_overlay = $('<div id="slider-opacity-overlay" style="position: absolute; right: 10px; top: 370px; z-index: 9999; width: 100px">').slider({
            //     min: 0,
            //     max: 100,
            //     value: 0,
            //     slide: function(event, ui) {
            //         $("#input-opacity-overlay").val(ui.value);
            //         change_overlay($('#couleur-overlay').val() + ',' + ui.value / 100);
            //     }
            // });
            // $(editor).append(slider_opactity_overlay);
            //
            // var $el = $('<input id="input-opacity-overlay" type="number" value="0" min="0" max="100" step="1" style="position: absolute; right: 10px; top: 390px; z-index: 9999;">').on('keyup', function() {
            //     slider_opactity_overlay.slider('value', $(this).val());
            //     change_overlay($('#couleur-overlay').val() + ',' + $(this).val() / 100);
            // });
            // $(editor).append($el);

            //Couleur de fond
            $(editor).append('<p style="position: absolute; right: 10px; top: 430px; z-index: 9999; color: #fff;">Couleur de fond</p>');
            //@TODO : COLORPICKER BACKGROUND
            var $el = $('<input id="couleur-background" value="transparent" type="text" style="position: absolute; right: 10px; top: 460px; z-index: 9999;">').on('change, keyup', function() {
                change_background($('#couleur-background').val());
            });
            $(editor).append($el);

            //Rotation
            var $el = $('<div id="slider-rotate" style="position: absolute; right: 10px; top: 550px; z-index: 9999;"></div>').roundSlider({
                radius: 50,
                sliderType: "default",
                min: 0,
                max: 360,
                value: 0,
                startAngle: 100,
                drag: 'rotateImage',
                change: 'rotateImage',
                tooltipFormat: function(args) {
                    return args.value + '°';
                }
            });
            $(editor).append($el);

            // Bouton d'alignement
            var $el = $('<button class="btn btn-primary btn-sm" style="position: absolute; right: 130px; top: 10px; z-index: 9999;"></button>').on('click', function() {
                cropper.moveTo(
                    (cropper.getCropBoxData().left + (cropper.getCropBoxData().width - cropper.getCanvasData().width)),
                    (cropper.getCropBoxData().top)
                );
            });
            $(editor).append($el);
            var $el = $('<button class="btn btn-primary btn-sm" style="position: absolute; right: 150px; top: 10px; z-index: 9999;"></button>').on('click', function() {
                cropper.moveTo(
                    (cropper.getCropBoxData().left + (cropper.getCropBoxData().width - cropper.getCanvasData().width) / 2),
                    (cropper.getCropBoxData().top)
                );
            });
            $(editor).append($el);
            var $el = $('<button class="btn btn-primary btn-sm" style="position: absolute; right: 170px; top: 10px; z-index: 9999;"></button>').on('click', function() {
                cropper.moveTo(
                    (cropper.getCropBoxData().left),
                    (cropper.getCropBoxData().top)
                );
            });
            $(editor).append($el);

            var $el = $('<button class="btn btn-primary btn-sm" style="position: absolute; right: 130px; top: 30px; z-index: 9999;"></button>').on('click', function() {
                cropper.moveTo(
                    (cropper.getCropBoxData().left + (cropper.getCropBoxData().width - cropper.getCanvasData().width)),
                    (cropper.getCropBoxData().top + (cropper.getCropBoxData().height - cropper.getCanvasData().height) / 2)
                );
            });
            $(editor).append($el);
            var $el = $('<button class="btn btn-primary btn-sm" style="position: absolute; right: 150px; top: 30px; z-index: 9999;"></button>').on('click', function() {
                cropper.moveTo(
                    (cropper.getCropBoxData().left + (cropper.getCropBoxData().width - cropper.getCanvasData().width) / 2),
                    (cropper.getCropBoxData().top + (cropper.getCropBoxData().height - cropper.getCanvasData().height) / 2)
                );
            });
            $(editor).append($el);
            var $el = $('<button class="btn btn-primary btn-sm" style="position: absolute; right: 170px; top: 30px; z-index: 9999;"></button>').on('click', function() {
                cropper.moveTo(
                    (cropper.getCropBoxData().left),
                    (cropper.getCropBoxData().top + (cropper.getCropBoxData().height - cropper.getCanvasData().height) / 2)
                );
            });
            $(editor).append($el);

            var $el = $('<button class="btn btn-primary btn-sm" style="position: absolute; right: 130px; top: 50px; z-index: 9999;"></button>').on('click', function() {
                cropper.moveTo(
                    (cropper.getCropBoxData().left + (cropper.getCropBoxData().width - cropper.getCanvasData().width)),
                    (cropper.getCropBoxData().top + (cropper.getCropBoxData().height - cropper.getCanvasData().height))
                );
            });
            $(editor).append($el);
            var $el = $('<button class="btn btn-primary btn-sm" style="position: absolute; right: 150px; top: 50px; z-index: 9999;"></button>').on('click', function() {
                cropper.moveTo(
                    (cropper.getCropBoxData().left + (cropper.getCropBoxData().width - cropper.getCanvasData().width) / 2),
                    (cropper.getCropBoxData().top + (cropper.getCropBoxData().height - cropper.getCanvasData().height))
                );
            });
            $(editor).append($el);
            var $el = $('<button class="btn btn-primary btn-sm" style="position: absolute; right: 170px; top: 50px; z-index: 9999;"></button>').on('click', function() {
                cropper.moveTo(
                    (cropper.getCropBoxData().left),
                    (cropper.getCropBoxData().top + (cropper.getCropBoxData().height - cropper.getCanvasData().height))
                );
            });
            $(editor).append($el);

            cropper = new Cropper(image, {
                viewMode: 0,
                dragMode: 'move',
                responsive: true,
                toggleDragModeOnDblclick: false,
                restore: false,
                guides: false,
                center: false,
                highlight: false,
                cropBoxMovable: false,
                cropBoxResizable: false,
                zoomOnWheel: true,
                wheelZoomRatio: 0.01,
                background: false,
                ready: function(el) {
                    cropper_is_ready = true;
                    calcul_taille_cropbox();
                }
            });
        }

        function rotateImage(e) {
            var rotate = cropper.getImageData().rotate;
            if (typeof rotate == 'undefined') {
                rotate = 0;
            }
            cropper.rotate(e.value - rotate);
        }

        function calcul_taille_cropbox() {
            if (cropper_is_ready) {
                var container = cropper.getContainerData();
                var data = {};
                data.width = width_final;
                data.height = height_final;

                var marge_width = 400;
                var marge_height = 200;

                if (container.width - marge_width < width_final) {
                    data.width = container.width - marge_width;
                    data.height = height_final * data.width / width_final;
                }
                if (container.height - marge_height < data.height) {
                    data.height = container.height - marge_height;
                    data.width = data.height * width_final / height_final;
                }
                data.left = ((container.width - data.width) / 2);
                data.top = ((container.height - data.height) / 2);
                cropper.setCropBoxData(data);

                //Zoom de l'image pour s'adpater au mieux en largeur ou en hauteur
                cropper.zoomTo(1);
                var ratio_width = cropper.getCropBoxData().width / cropper.getCanvasData().naturalWidth
                var ratio_height = cropper.getCropBoxData().height / cropper.getCanvasData().naturalHeight;
                if (ratio_width < ratio_height) {
                    cropper.zoomTo(ratio_width);
                } else {
                    cropper.zoomTo(ratio_height);
                }
                cropper.moveTo(
                    (cropper.getCropBoxData().left + (cropper.getCropBoxData().width - cropper.getCanvasData().width) / 2),
                    (cropper.getCropBoxData().top + (cropper.getCropBoxData().height - cropper.getCanvasData().height) / 2));


                //Texte d'information d'affichage
                var ratio_zoom = data.width * 100 / width_final;

                var texte_info_taille = 'Dimension finale : ' + width_final + 'px x ' + height_final + 'px | Zoom de la zone de redimension : ' + ratio_zoom.toFixed(0) + '%';
                if ($('#info-taille-image').length == 0) {
                    $(editor).prepend('<p id="info-taille-image" style="color: rgb(255, 255, 255); text-align: center; z-index: 9999;">' + texte_info_taille + '</p>');
                } else {
                    $('#info-taille-image').html(texte_info_taille);
                }
            }
        }

        function change_opacite_image(opacite) {
            $('.cropper-canvas img, .cropper-view-box img').css('opacity', opacite);
        }

        function change_overlay(color_rgba) {
            if ($('#style_overlay').length == 0) {
                $('head').append('<style id="style_overlay" type="text/css"></style>');
            }
            $('#style_overlay').html("span.cropper-view-box:after { content: ''; position: absolute; top: 0;  right: 0;  bottom: 0;  left: 0;  background-color: " + color_rgba + ";}");

        }

        function change_background(color) {
            if ($('#style_background').length == 0) {
                $('head').append('<style id="style_background" type="text/css"></style>');
            }
            $('#style_background').html(".cropper-crop-box { background-color: " + color + ";}</style>");

        }
    </script>
</head>

<body>
    <div id="zone-upload">
        <div class="dz-default dz-message">
            <div class="dropzone-previews"></div>
            Cliquez ici pour prendre les photos ou déposer vos fichiers
        </div>
    </div>

    <br />OU<br /><br /><button id="file_exist">Fichier existant</button>

    <!--<form method="post" enctype="multipart/form-data">
        <input id="file" name="file" type="file" />
        <input id="cropdata" name="cropdata" type="text" />
        <button type="submit">Valider</button>
    </form>-->
</body>

</html>