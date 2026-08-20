import {Banner, Link} from '@shopify/polaris';
import React from 'react';

export function AlertBanner() {
    return (
        <Banner
            title={`Important Information`}
            // action={{content: 'Review risk analysis'}}
            tone="critical"
        >
            <p>
                {'You need to choose the subscription plan!'}
            </p>
        </Banner>
    );
}
