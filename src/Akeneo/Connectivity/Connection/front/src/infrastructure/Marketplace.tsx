import React, {StrictMode} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {SelectUserProfilePage} from '../connect/pages/SelectUserProfilePage';
import {MarketplacePage} from '../connect/pages/MarketplacePage';

export const Marketplace = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <Switch>
                    <Route path='/connect/app-store/profile'>
                        <SelectUserProfilePage />
                    </Route>
                    <Route path='/connect/app-store'>
                        <MarketplacePage />
                    </Route>
                </Switch>
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));
