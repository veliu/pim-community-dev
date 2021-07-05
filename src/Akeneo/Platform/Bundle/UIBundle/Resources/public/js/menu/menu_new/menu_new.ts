'use strict';

import {ViewOptions} from 'backbone';
import {NavigationEntry, PimNavigation, SubNavigation, SubNavigationSection} from '../../PimNavigation';
import View from '../../view/base-interface';
import React from 'react';
import * as DSM from 'akeneo-design-system';

const BaseForm = require('pim/form');
const _ = require('underscore');
const template = require('pim/template/menu/menu');
const mediator = require('oro/mediator');

type EntryView = View & {
  config: {
    title: string;
    to?: string;
    isLandingSectionPage?: boolean;
    tab?: string;
    icon: string;
  };
  items: SubEntry[];
  sections: any[];
};

// @fixme Define what is an entry column
type EntryColumnView = View & {
  config: {
    title: string;
    to?: string;
    tab?: string;
    navigationTitle?: string;
    backLink: {
      title: string;
      route: string;
    }
  };
  navigationItems: SubEntry[];
  sections: any[];
};

/**
 * Base extension for menu
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Menu extends BaseForm {
  template = _.template(template);
  activeEntryCode;
  activeSubEntryCode;

  constructor(options?: ViewOptions<any>) {
    super({
      ...options,
      className: 'AknHeader',
    });

    this.activeEntryCode = null;
    this.activeSubEntryCode = null;
  }

  configure() {
    mediator.on('pim_menu:highlight:tab', this.highlightTab, this);
    mediator.on('pim_menu:highlight:item', this.highlightItem, this);
    mediator.on('pim_menu:hide', this.hideSubNavigation, this);

    return super.configure();
  }

  /**
   * {@inheritdoc}
   */
  render() {
    if (!this.configured) {
      return this;
    }

    this.renderReact(
      PimNavigation,
      {
        entries: this.findMainEntries(),
        activeEntryCode: this.activeEntryCode,
        activeSubEntryCode: this.activeSubEntryCode
      },
      this.el
    );

    return this;
  }

  /**
   * {@inheritdoc}
   */
  renderExtension(extension: any) {
    if (
      !_.isEmpty(extension.options.config) &&
      (!extension.options.config.to || extension.options.config.isLandingSectionPage) &&
      _.isFunction(extension.hasChildren) &&
      !extension.hasChildren()
    ) {
      return;
    }

    super.renderExtension(extension);
  }

  findMainEntries(): NavigationEntry[] {
    const navigationEntriesExtensions = Object.values(this.extensions).filter((extension: View) => {
      if (extension.targetZone !== 'mainMenu') {
        return false;
      }

      return extension.code !== 'pim-menu-logo';
    });

    navigationEntriesExtensions.sort((entryA: any, entryB: any) => {
      return entryA.position - entryB.position;
    });

    const entries: NavigationEntry[] = navigationEntriesExtensions.map((extension: EntryView) => {
      const {title, isLandingSectionPage, icon} = extension.config;

      return {
        code: extension.code,
        title: title,
        disabled: false,
        route: this.findEntryRoute(extension),
        // @ts-ignore
        icon: DSM[icon] && React.createElement(DSM[icon]),
        subNavigations: this.findMainEntrySubNavigations(extension.code),
        isLandingSectionPage: isLandingSectionPage ?? false,
      };
    });

    return entries;
  }

  findMainEntrySubNavigations(entryCode: string): SubNavigation[] {
    const columns = Object.values(this.extensions).filter((extension: EntryView) => {
      // @todo Ensure that we should use "tab" (it's not always defined. ex: pim-menu-connection-column)
      return extension.targetZone === 'column' && extension.config.tab === entryCode;
    });

    return columns.map((column: EntryColumnView) => {
      column.sections.sort((sectionA: any, sectionB: any) => {
        return sectionA.position - sectionB.position;
      });

      const sections: SubNavigationSection[] = column.sections.map(section => {
        return {
          code: section.code,
          title: section.title,
        }
      });

      let backLink;
      if (column.config.backLink) {
        backLink = {
          title: column.config.backLink.title,
          route: column.config.backLink.route,
        }
      }

      return {
        title: column.config.navigationTitle,
        sections: sections,
        // @fixme Handle columns without title
        entries: column.navigationItems,
        backLink: backLink,
      };
    });
  }

  findEntryRoute(entry: EntryView): string {
    if (entry.config.to !== undefined) {
      return entry.config.to;
    }

    if (entry.items.length > 0) {
      entry.items.sort((itemA: SubEntry, itemB: SubEntry) => {
        return itemA.position - itemB.position;
      });

      return entry.items[0].route;
    }

    return 'pim_settings_index';
  }

  highlightTab(event: any) {
    this.activeEntryCode = event.extension;

    this.render();
  }

  highlightItem(event: any) {
    this.activeSubEntryCode = event.extension || '';

    this.render();
  }

  hideSubNavigation() {
    this.activeSubEntryCode = '';

    this.render();
  }
}

export = Menu;
