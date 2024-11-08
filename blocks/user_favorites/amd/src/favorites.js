// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Tested in Moodle 3.5 / 3.9 / 4.2 / 4.3
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @copyright 2018 MFreak.nl
 * @author    Luuk Verhoeven
 **/

/* eslint no-unused-expressions: "off", no-console:off, no-invalid-this:"off",no-script-url:"off", block-scoped-var: "off" */
define(['jquery', 'core/ajax', 'core/notification', 'core/log', 'core/sortable_list'],
    function($, Ajax, Notification, Log, SortableList) {

        /**
         * Opts that are possible to set.
         *
         * @type {{id: number, debugjs: boolean}}
         */
        let opts = {
            debugjs: true, id: 0, url: '', hash: ''
        };

        /**
         * Set options base on listed options
         * @param {object} options
         */
        const setOptions = function(options) {
            "use strict";
            let key, vartype;
            for (key in opts) {
                if (opts.hasOwnProperty(key) && options.hasOwnProperty(key)) {

                    // Casting to prevent errors.
                    vartype = typeof opts[key];
                    if (vartype === "boolean") {
                        opts[key] = Boolean(options[key]);
                    } else if (vartype === 'number') {
                        opts[key] = Number(options[key]);
                    } else if (vartype === 'string') {
                        opts[key] = String(options[key]);
                    }
                    // Skip all other types.
                }
            }
        };

        const attachDragDropHandlers = function() {
            $('ol#block_user_favorites-items > li').on(SortableList.EVENTS.DRAGEND, function(evt, info) {
                if (info.positionChanged) {
                    favoritesModule.setOrder();
                }
                evt.stopPropagation();
            });
            $('ol#block_user_favorites-items > li').on(SortableList.EVENTS.DRAGSTART, (evt, info) => {
                setTimeout(() => {
                    $('.sortable-list-is-dragged').width(info.element.width());
                }, 501);
            });
        };

        const favoritesModule = {

            /**
             * Add or update a url
             *
             * @param {object} data
             * @param {string} title
             */
            setUrl: function(data, title) {

                if (data.hash === null) {
                    Notification.exception(new Error('No hash found'));
                    return;
                }

                if (data.hasOwnProperty('url') && data.url === null) {
                    delete data.url;
                }

                // Remove not correct closed confirm dialogs.
                $('.modal').remove();

                Notification.confirm(M.util
                        .get_string('javascript:set_title', 'block_user_favorites'),
                    '<input class="form-control" id="favorite-url" value="'
                    + title + '">', M.util.get_string('javascript:yes', 'block_user_favorites'),
                    M.util.get_string('javascript:no', 'block_user_favorites'), function() {

                        let request = Ajax.call([{
                            methodname: 'block_user_favorites_set_url', args: {
                                hash: data.hash,
                                optional: {
                                    url: data.url,
                                }, title: $('.modal-dialog #favorite-url').val(),
                                blockid: opts.id,
                            }
                        }]);

                        request[0].done(function(response) {
                            Log.log(response);
                            favoritesModule.reload();
                        }).fail(Notification.exception);
                    });
            },

            /**
             * Add or update a url
             */
            setOrder: function() {
                $('ol#block_user_favorites-items li').each(function(index) {
                    Ajax.call([{
                        methodname: 'block_user_favorites_set_order', args: {
                            hash: $(this).data('hash'), sortorder: index
                        }
                    }]);
                });
            },

            /**
             * Delete a url
             *
             * @param {object} data
             */
            remove: function(data) {

                let request = Ajax.call([{
                    methodname: 'block_user_favorites_delete_url', args: {
                        hash: data.hash, blockid: opts.id,
                    }
                }]);

                request[0].done(function(response) {
                    Log.log(response);
                    favoritesModule.reload();
                }).fail(Notification.exception);
            },

            /**
             * Reload the block
             */
            reload: function() {

                let request = Ajax.call([{
                    methodname: 'block_user_favorites_content', args: {
                        url: opts.url, blockid: opts.id,
                    }
                }]);

                request[0].done(function(response) {
                    Log.log(response);
                    $('.block_user_favorites .content').html(response.content);
                    // Re-attach drag/drop callback on the new content to ensure sorting still works after content refresh.
                    attachDragDropHandlers();
                }).fail(Notification.exception);

            },

            /**
             * Init event triggers.
             */
            init: function() {
                Log.log('Init block_user_favorites');

                $('.block_user_favorites').on('click', '#block_user_favorites_set', function() {

                    // Set current as favorite.
                    favoritesModule.setUrl({
                        'hash': opts.hash,
                        'url': window.location.href,
                    }, $('title').text());

                }).on('click', '#block_user_favorites_delete', function() {
                    // Delete current pages from favorites.
                    favoritesModule.remove({
                        'hash': opts.hash,
                    });

                }).on('click', '.fa-remove', function() {
                    // Remove a fav in the list.
                    favoritesModule.remove($(this).closest('li').data());

                }).on('click', '.fa-edit', function() {
                    // Edit a fav int the list.
                    let data = $(this).parent().parent().data();
                    Log.log('.fa-edit');
                    Log.log(data);
                    data.url = null;
                    favoritesModule.setUrl(data, $(this).parent().parent().find('a').text());
                });
                // Instantiate new SortableList component. this only needs to happen once (i.e. not on refresh again).
                new SortableList('ol#block_user_favorites-items');
                // Attach the drag/drop callbacks.
                attachDragDropHandlers();
            }
        };

        return {
            /**
             * Init
             *
             * @param {object} args
             */
            initialise: function(args) {

                // Load the args passed from PHP.
                setOptions(args);

                // Set internal debug console.
                Log.log(opts.debugjs);

                $.noConflict();
                $(document).ready(function() {
                    Log.log('Block User Favorites v1.2');
                    favoritesModule.init();
                });
            }
        };
    });
