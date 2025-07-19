$(document).ready(function() {
    // Custom validation method for future dates
    $.validator.addMethod("futureDate", function(value, element) {
        const today = new Date();
        const inputDate = new Date(value);
        return this.optional(element) || inputDate >= today;
    }, "La date doit être aujourd'hui ou dans le futur");

    // Custom validation method for date after another date
    $.validator.addMethod("dateAfter", function(value, element, param) {
        const startDate = new Date($(param).val());
        const endDate = new Date(value);
        return this.optional(element) || endDate > startDate;
    }, "La date d'expiration doit être postérieure à la date de lancement");

    // Initialize form validation
    $("#Form").validate({
        rules: {
            categorie_name: {
                required: true,
                minlength: 3,
                pattern: /^[A-Za-z\s]+$/
            },
            brand_name: {
                required: true,
                minlength: 3,
                pattern: /^[A-Za-z\s]+$/
            },
            brand_cover: {
                required: true,
                accept: "image/*"
            },
            product_name: {
                required: true,
                minlength: 3
            },
            type: {
                required: true
            },
            Category: {
                required: true
            },
            description: {
                required: true,
                minlength: 10
            },
            brand: {
                required: true
            },
            price: {
                required: true,
                number: true,
                min: 0.01
            },
            taux_reduction: {
                required: function() {
                    return $('#en_promo').is(':checked');
                },
                number: true,
                min: 0,
                max: 100
            },
            "taille_vetement[]": {
                required: function() {
                    return $('#vetement-fields').is(':visible');
                }
            },
            "couleur_vetement[]": {
                required: function() {
                    return $('#vetement-fields').is(':visible');
                }
            },
            "quantity_vetement[]": {
                required: function() {
                    return $('#vetement-fields').is(':visible');
                },
                min: 0
            },
            "format[]": {
                required: function() {
                    return $('#alimentaire-fields').is(':visible');
                }
            },
            "saveur[]": {
                required: function() {
                    return $('#alimentaire-fields').is(':visible');
                }
            },
            "price_alimentaire[]": {
                required: function() {
                    return $('#alimentaire-fields').is(':visible');
                },
                number: true,
                min: 0.01
            },
            "quantity_alimentaire[]": {
                required: function() {
                    return $('#alimentaire-fields').is(':visible');
                },
                min: 0
            },
            "taille_accessoire[]": {
                required: function() {
                    return $('#accessoire-fields').is(':visible');
                }
            },
            "couleur_accessoire[]": {
                required: function() {
                    return $('#accessoire-fields').is(':visible');
                }
            },
            "quantity_accessoire[]": {
                required: function() {
                    return $('#accessoire-fields').is(':visible');
                },
                min: 0
            },
            genre: {
                required: function() {
                    return $('#vetement-fields').is(':visible');
                }
            },
            nom_pack: {
                required: true,
                minlength: 3,
                pattern: /^[A-Za-z\s]+$/
            },
            produits_pack: {
                required: true,
                minlength: 3,
                pattern: /^[A-Za-z\s]+$/
            },
            date_debut: {
                required: true,
                date: true,
                futureDate: true
            },
            date_fin: {
                required: true,
                date: true,
                dateAfter: "input[name='date_debut']"
            },
            total_pack: {
                required: true,
                number: true,
                min: 0.01
            },
            cover_pack: {
                required: true,
                accept: "image/*"
            },
            code: {
                required: true,
                minlength: 3,
                pattern: /^[A-Za-z0-9]+$/
            },
            reduction: {
                required: true,
                number: true,
                min: 0.01,
                max: 100
            },
            date_lancement: {
                required: true,
                date: true,
                futureDate: true
            },
            date_expiration: {
                required: true,
                date: true,
                dateAfter: "input[name='date_lancement']"
            },
        },
        messages: {
            categorie_name: {
                required: "Veuillez entrer le nom de la catégorie",
                minlength: "Le nom doit contenir au moins 3 caractères",
                pattern: "Seules les lettres et espaces sont autorisés"
            },
            brand_name: {
                required: "Veuillez entrer le nom de la marque",
                minlength: "Le nom doit contenir au moins 3 caractères",
                pattern: "Seules les lettres et espaces sont autorisés"
            },
            brand_cover: {
                required: "Veuillez télécharger une image",
                accept: "Format d'image invalide"
            },
            product_name: {
                required: "Veuillez entrer le nom du produit",
                minlength: "Le nom doit contenir au moins 3 caractères"
            },
            type: "Veuillez sélectionner un type de produit",
            Category: "Veuillez sélectionner une catégorie",
            description: {
                required: "Veuillez entrer une description",
                minlength: "La description doit contenir au moins 10 caractères"
            },
            brand: "Veuillez sélectionner une marque",
            price: {
                required: "Veuillez entrer un prix",
                number: "Veuillez entrer un nombre valide",
                min: "Le prix doit être supérieur à 0"
            },
            taux_reduction: {
                required: "Veuillez entrer un taux de réduction",
                number: "Veuillez entrer un nombre valide",
                min: "Le taux doit être au moins 0%",
                max: "Le taux ne peut pas dépasser 100%"
            },
            "taille_vetement[]": "Veuillez sélectionner une taille",
            "couleur_vetement[]": "Veuillez entrer une couleur",
            "quantity_vetement[]": {
                required: "Veuillez entrer une quantité",
                min: "La quantité ne peut pas être négative"
            },
            "format[]": "Veuillez entrer un format",
            "saveur[]": "Veuillez entrer une saveur",
            "price_alimentaire[]": {
                required: "Veuillez entrer un prix",
                number: "Veuillez entrer un nombre valide",
                min: "Le prix doit être supérieur à 0"
            },
            "quantity_alimentaire[]": {
                required: "Veuillez entrer une quantité",
                min: "La quantité ne peut pas être négative"
            },
            "taille_accessoire[]": "Veuillez entrer une taille",
            "couleur_accessoire[]": "Veuillez entrer une couleur",
            "quantity_accessoire[]": {
                required: "Veuillez entrer une quantité",
                min: "La quantité ne peut pas être négative"
            },
            genre: "Veuillez sélectionner un genre",
            nom_pack: {
                required: "Veuillez entrer le nom du pack",
                minlength: "Le nom doit contenir au moins 3 caractères",
                pattern: "Seules les lettres et espaces sont autorisés"
            },
            produits_pack: {
                required: "Veuillez entrer les produits du pack",
                minlength: "Les produits doivent contenir au moins 3 caractères",
                pattern: "Seules les lettres et espaces sont autorisés"
            },
            date_debut: {
                required: "Veuillez sélectionner la date de début",
                date: "Veuillez entrer une date valide"
            },
            date_fin: {
                required: "Veuillez sélectionner la date de fin",
                date: "Veuillez entrer une date valide"
            },
            total_pack: {
                required: "Veuillez entrer le prix du pack",
                number: "Veuillez entrer un montant valide",
                min: "Le prix doit être supérieur à 0"
            },
            cover_pack: {
                required: "Veuillez sélectionner une image",
                accept: "Format d'image invalide"
            },
            code: {
                required: "Veuillez entrer un code promo",
                minlength: "Le code doit contenir au moins 3 caractères",
                pattern: "Seules les lettres et chiffres sont autorisés"
            },
            reduction: {
                required: "Veuillez entrer un pourcentage de réduction",
                number: "Veuillez entrer un nombre valide",
                min: "La réduction doit être supérieure à 0",
                max: "La réduction ne peut pas dépasser 100%"
            },
            date_lancement: {
                required: "Veuillez sélectionner une date de lancement",
                date: "Veuillez entrer une date valide"
            },
            date_expiration: {
                required: "Veuillez sélectionner une date d'expiration",
                date: "Veuillez entrer une date valide"
            }
        },
        errorElement: "div",
        errorClass: "text-danger",
        errorPlacement: function(error, element) {
            if (element.attr("name") === "genre") {
                error.insertAfter($(".gender-container"));
            } else if (element.hasClass("size-select") || element.hasClass("size-type-select")) {
                error.insertAfter(element.closest(".input-group"));
            } else if (element.attr("type") === "file") {
                error.insertAfter(element.closest(".color-images"));
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function(element) {
            $(element).addClass("is-invalid");
            $(element).closest(".form-group").addClass("has-error");
            if ($(element).hasClass("size-select") || $(element).hasClass("size-type-select")) {
                $(element).closest(".input-group").addClass("is-invalid");
            }
        },
        unhighlight: function(element) {
            $(element).removeClass("is-invalid");
            $(element).closest(".form-group").removeClass("has-error");
            if ($(element).hasClass("size-select") || $(element).hasClass("size-type-select")) {
                $(element).closest(".input-group").removeClass("is-invalid");
            }
        },
        submitHandler: function(form) {
            const selectedType = $("#type-select").val();
            let isValid = true;

            // Validate variant existence based on product type
            if (selectedType === 'vetement' && $("#vetement-variants .variant-row").length === 0) {
                alert("Veuillez ajouter au moins une variante de taille/couleur pour les vêtements");
                isValid = false;
            } else if (selectedType === 'alimentaire' && $("#alimentaire-variants .variant-row").length === 0) {
                alert("Veuillez ajouter au moins une variante de format/saveur pour les produits alimentaires");
                isValid = false;
            } else if (selectedType === 'accessoire' && $("#accessoire-variants .variant-row").length === 0) {
                alert("Veuillez ajouter au moins une variante de taille/couleur pour les accessoires");
                isValid = false;
            }

            if (isValid) {
                form.submit();
            }
        }
    });

    // Event handlers for dynamic validation
    $('#type-select').change(function() {
        $("#Form").validate().form();
    });

    $('#en_promo').change(function() {
        $("#Form").validate().form();
    });

    $(document).on('keyup change', '.variant-row input, .variant-row select', function() {
        $(this).valid();
    });
});