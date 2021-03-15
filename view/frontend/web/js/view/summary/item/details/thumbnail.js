/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

 define(['uiComponent'], function (Component) {
    'use strict';

    var imageData = window.checkoutConfig.imageData;

    function getValues(obj, key) {
        let objects = [];
        for (let i in obj) {
            if (!obj.hasOwnProperty(i)) continue;
            if (typeof obj[i] == 'object') {
                objects = objects.concat(getValues(obj[i], key));
            } else if (i == key) {
                objects.push(obj[i]);
            }
        }
        return objects;
    }

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details/thumbnail'
        },
        displayArea: 'before_details',
        imageData: imageData,

        /**
         * @param {Object} item
         * @return {Array}
         */
        getImageItem: function (item) {
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']];
            }

            return [];
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getSrc: function (item) {
            let currentItem = window.checkoutConfig.quoteItemData.find((a) => { return parseInt(a.item_id) === parseInt(item['item_id']) })
            if (currentItem.product_id) {
                let productId = currentItem.product_id;
                let pprintSpProjects = localStorage.hasOwnProperty('pprint-sp') ? JSON.parse(localStorage.getItem('pprint-sp')) : null;
                let pprintSpProject = pprintSpProjects ? getValues(pprintSpProjects, productId)[0] : "";

                if (pprintSpProject) {
                    let projectId = JSON.parse(decodeURIComponent(pprintSpProject)).projectId

                    if (!projectId) {
                        if (this.imageData[item['item_id']]) {
                            return this.imageData[item['item_id']].src;
                        } else {
                            return null;
                        }
                    }
                    let getProjectId = projectId ? projectId : JSON.parse(localStorage.pp_w2p_projects)[productId].projectId;

                    let _n = Math.random();
                    let _prev = `https://s3-eu-west-1.amazonaws.com/pitchprint.io/previews/${getProjectId}_1.jpg?
                                rand=${_n}`;
                    return _prev
                }
            }


            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']].src;
            }

            return null;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getWidth: function (item) {
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']].width;
            }

            return null;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getHeight: function (item) {
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']].height;
            }

            return null;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getAlt: function (item) {
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']].alt;
            }

            return null;
        }
    });
});
