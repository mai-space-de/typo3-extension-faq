/**
 * FAQ Accordion / Tabs / Search – shared JavaScript
 */
(function () {
    'use strict';

    /** Toggle an accordion item open/closed */
    function toggleAccordion(button) {
        var answer = document.getElementById(button.getAttribute('aria-controls'));
        if (!answer) { return; }
        var expanded = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', String(!expanded));
        if (expanded) {
            answer.setAttribute('hidden', 'hidden');
        } else {
            answer.removeAttribute('hidden');
        }
    }

    /** Switch active tab */
    function activateTab(tabs, panels, selectedTab) {
        tabs.forEach(function (tab) {
            var isSelected = tab === selectedTab;
            tab.setAttribute('aria-selected', String(isSelected));
            tab.classList.toggle('faq-tabs__tab--active', isSelected);
            var panel = document.getElementById(tab.getAttribute('aria-controls'));
            if (panel) {
                panel.classList.toggle('faq-tabs__panel--active', isSelected);
                if (isSelected) {
                    panel.removeAttribute('hidden');
                } else {
                    panel.setAttribute('hidden', 'hidden');
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {

        /** Accordion toggle buttons */
        document.querySelectorAll('.faq-accordion__toggle').forEach(function (btn) {
            btn.addEventListener('click', function () { toggleAccordion(btn); });
        });

        /** Category tab buttons */
        document.querySelectorAll('.faq-tabs').forEach(function (widget) {
            var tabs   = Array.prototype.slice.call(widget.querySelectorAll('[role="tab"]'));
            var panels = Array.prototype.slice.call(widget.querySelectorAll('[role="tabpanel"]'));
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () { activateTab(tabs, panels, tab); });
            });
        });
    });
}());
