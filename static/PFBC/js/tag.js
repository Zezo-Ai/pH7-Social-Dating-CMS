/*
 * Progressive enhancement for PFBC's comma-separated Tag element.
 * Author: Pierre-Henry Soria <hello@ph7builder.com>
 * License: MIT License; See LICENSE.md and COPYRIGHT.md in the root directory.
 */
(function () {
    'use strict';

    // PFBC can include this asset once for each form on the page.
    if (window.pH7TagField) {
        return;
    }

    function splitTags(sValue) {
        return sValue.split(',').map(function (sTag) {
            return sTag.trim();
        }).filter(function (sTag) {
            return sTag !== '';
        });
    }

    function init(oInput) {
        if (!oInput || oInput.dataset.tagBound) {
            return;
        }
        oInput.dataset.tagBound = 'true';

        var oForm = oInput.form;
        var bRequired = oInput.required;
        var sPlaceholder = oInput.placeholder;
        var iMax = oInput.maxLength;
        var bComposing = false;
        var aTags = splitTags(oInput.value);
        var oField = document.createElement('div');
        var oList = document.createElement('span');
        var oValue = document.createElement('input');
        var oError = document.createElement('small');
        var oHelp = document.getElementById(oInput.id + '_tag_help');

        oField.className = 'pfbc-tags';
        oList.className = 'pfbc-tags-list';
        oList.setAttribute('role', 'list');
        oValue.type = 'hidden';
        oValue.name = oInput.name;
        if (oInput.hasAttribute('form')) {
            oValue.setAttribute('form', oInput.getAttribute('form'));
        }
        oError.id = oInput.id + '_tag_error';
        oError.className = 'pfbc-tags-error';
        oError.setAttribute('aria-live', 'polite');
        oInput.setAttribute('aria-describedby', (oInput.getAttribute('aria-describedby') || '') + ' ' + oError.id);
        oInput.removeAttribute('name');
        // The limit applies to the whole submitted list, not just the next tag.
        oInput.removeAttribute('maxlength');
        oInput.classList.add('pfbc-tags-input');
        oInput.value = '';
        oInput.parentNode.insertBefore(oField, oInput);
        oField.append(oList, oInput, oValue);
        oField.after(oError);

        function pendingTags() {
            var aValues = aTags.slice();
            splitTags(oInput.value).forEach(function (sTag) {
                if (aValues.indexOf(sTag) === -1) {
                    aValues.push(sTag);
                }
            });
            return aValues;
        }

        function syncValue() {
            // Keep pending text too: both native submit and PFBC Ajax serialize this input.
            oValue.value = pendingTags().join(',');
            oValue.disabled = oInput.disabled;
            oInput.required = bRequired && aTags.length === 0;
            var sError = '';
            if (!oInput.disabled && !oInput.readOnly) {
                if (iMax >= 0 && Array.from(oValue.value).length > iMax) {
                    sError = oInput.dataset.tagLimit;
                } else if (bRequired && !oValue.value) {
                    sError = oInput.dataset.tagRequired;
                }
            }
            oInput.setCustomValidity(sError);
            // Leave empty required fields quiet until the user attempts to submit.
            oError.textContent = oValue.value ? sError : '';
            oInput.setAttribute('aria-invalid', oError.textContent ? 'true' : 'false');
            oField.classList.toggle('pfbc-tags-invalid', Boolean(oError.textContent));
            return sError === '';
        }

        function renderTags() {
            oList.textContent = '';
            aTags.forEach(function (sTag, iIndex) {
                var oTag = document.createElement('span');
                var oLabel = document.createElement('span');
                var oRemove = document.createElement('button');
                oTag.className = 'pfbc-tag';
                oTag.setAttribute('role', 'listitem');
                oLabel.className = 'pfbc-tag-label';
                oLabel.textContent = sTag;
                oRemove.type = 'button';
                oRemove.className = 'pfbc-tag-remove';
                oRemove.textContent = '×';
                oRemove.setAttribute('aria-label', oInput.dataset.tagRemove.replace('%0%', sTag));
                oRemove.disabled = oInput.disabled || oInput.readOnly;
                oRemove.addEventListener('click', function () {
                    if (oInput.disabled || oInput.readOnly) {
                        return;
                    }
                    aTags.splice(iIndex, 1);
                    renderTags();
                    syncValue();
                    oInput.focus();
                });
                oTag.append(oLabel, oRemove);
                oList.append(oTag);
            });
            var bInactive = oInput.disabled || oInput.readOnly;
            oField.classList.toggle('pfbc-tags-inactive', bInactive);
            oInput.placeholder = bInactive ? '' : sPlaceholder;
            if (oHelp) {
                oHelp.textContent = bInactive ? '' : oInput.dataset.tagHelp;
            }
        }

        function commitTags() {
            if (bComposing || oInput.disabled || oInput.readOnly || !syncValue()) {
                return;
            }
            if (!oInput.value.trim()) {
                return;
            }
            aTags = pendingTags();
            oInput.value = '';
            renderTags();
            syncValue();
        }

        oInput.addEventListener('keydown', function (oEvent) {
            if (bComposing || oEvent.isComposing || oEvent.keyCode === 229) {
                return;
            }
            if (oEvent.key === 'Enter') {
                oEvent.preventDefault();
                commitTags();
            } else if (oEvent.key === 'Backspace' && !oInput.value && oList.lastElementChild) {
                // Focus the remove button first; never silently delete a tag.
                oEvent.preventDefault();
                oList.lastElementChild.querySelector('button').focus();
            }
        });
        oInput.addEventListener('input', function () {
            syncValue();
            if (!bComposing && oInput.value.includes(',')) {
                commitTags();
            }
        });
        oInput.addEventListener('change', syncValue);
        oInput.addEventListener('invalid', function () {
            oError.textContent = oInput.validationMessage;
            oInput.setAttribute('aria-invalid', 'true');
            oField.classList.add('pfbc-tags-invalid');
        });
        oInput.addEventListener('compositionstart', function () { bComposing = true; });
        oInput.addEventListener('compositionend', function () {
            bComposing = false;
            syncValue();
        });
        oInput.addEventListener('blur', function (oEvent) {
            // Keep the clicked remove button in the DOM until its click is handled.
            if (!oField.contains(oEvent.relatedTarget)) {
                commitTags();
            }
        });
        oField.addEventListener('click', function (oEvent) {
            if (oEvent.target === oField) {
                oInput.focus();
            }
        });
        new MutationObserver(function () {
            renderTags();
            syncValue();
        }).observe(oInput, { attributes: true, attributeFilter: ['disabled', 'readonly'] });
        if (oForm) {
            oForm.addEventListener('reset', function (oEvent) {
                // The reset event precedes restoration of the input's default value.
                setTimeout(function () {
                    if (!oEvent.defaultPrevented) {
                        aTags = splitTags(oInput.defaultValue);
                        oInput.value = '';
                        bComposing = false;
                        renderTags();
                        syncValue();
                    }
                }, 0);
            });
        }
        renderTags();
        syncValue();
    }

    window.pH7TagField = { init: init };
})();
