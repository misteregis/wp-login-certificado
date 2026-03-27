jQuery(function ($) {
    let lastCNPJ = null;
    let timeout = null;
    let ajax = null;

    const $cnpjField = $("#cnpj");
    const $errorField = $("<p>", {
        id: "cnpj-error",
        text: "Este CNPJ já está em uso por ",
        css: {
            color: "#c51818",
            background: "#f6d8d8",
            padding: "0.1rem 0.3rem",
            borderRadius: "4px",
            fontSize: "80%",
            marginTop: "0.2rem",
            maxWidth: "max-content",
        }
    });

    function removeErrorField() {
        $("#cnpj-error").fadeOut(300, function () {
            $(this).remove();
        });
    }

    function formatCnpj(inputValue) {
        const document = inputValue.toString().replace(/\D/g, "");

        if (document.length <= 2) {
            return document;
        } else if (document.length <= 5) {
            return document.replace(/(\d{2})(\d+)/, "$1.$2");
        } else if (document.length <= 8) {
            return document.replace(/(\d{2})(\d{3})(\d+)/, "$1.$2.$3");
        } else if (document.length <= 12) {
            return document.replace(/(\d{2})(\d{3})(\d{3})(\d+)/, "$1.$2.$3/$4");
        } else {
            return document.slice(0, 14).replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{1,2})/, "$1.$2.$3/$4-$5");
        }
    }

    function verifyCNPJStatus() {
        let value = $cnpjField.val();
        let cnpj = value.replace(/\D/g, "");

        removeErrorField();

        if (cnpj.length !== 14 || value === lastCNPJ) {
            return;
        }

        lastCNPJ = value;

        if (ajax) {
            ajax.abort();
        }

        $cnpjField.data("value", value).prop("disabled", true).val("Verificando...");

        ajax = $.post(cnpjAjax.ajaxurl, {
            action: "check_cnpj",
            cnpj: cnpj,
            user_id: cnpjAjax.user_id
        }, function (res) {
            if (res.success && res.data.exists) {
                const $link = $("<a>", {
                    href: res.data.user.link,
                    text: res.data.user.login,
                    target: "_blank"
                });

                $("#cnpj").after($errorField.clone().append($link, "."));

                setTimeout(removeErrorField, 6000);
            }
        }).always(function () {
            $cnpjField.prop("disabled", false).val($cnpjField.data("value")).focus();
        });
    }

    // 🔹 Ao sair do campo
    $cnpjField.on("blur", verifyCNPJStatus);

    // 🔹 Enquanto digita (com debounce)
    $cnpjField.on("input", function () {
        clearTimeout(timeout);
        removeErrorField();

        $(this).val(formatCnpj($(this).val()));

        timeout = setTimeout(verifyCNPJStatus, 400);
    });

    $cnpjField.val(formatCnpj($cnpjField.val()));

    lastCNPJ = $cnpjField.val();
});