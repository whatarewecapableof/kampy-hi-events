import {describe, expect, it} from 'vitest';
import {readFileSync} from 'node:fs';

const source = (path: string) => readFileSync(new URL(path, import.meta.url), 'utf8');

// Source checks complement the browser pass without invoking any auth mutation.
describe('Kamp auth-page presentation', () => {
    const layout = source('./index.tsx');
    const styles = source('./Auth.module.scss');
    const login = source('../../routes/auth/Login/index.tsx');

    it('does not offer organizer self-registration from login', () => {
        expect(login).not.toMatch(/Sign up|auth\/register|Don't have an account/);
    });

    it('uses the established Kamp mark on a plain Kamp ground', () => {
        expect(layout).toContain('<BrandMark variant="light"/>');
        expect(styles).toContain('background: var(--kamp-cream)');
        expect(layout).not.toMatch(/FeaturePanel|allFeatures|showInfo|handleLogoClick/);
        expect(styles).not.toMatch(/nightlife-bg|gradient|glowEffect/);
    });

    it('retains attribution, locale selection and the authenticated redirect', () => {
        expect(layout).toContain('<PoweredByFooter />');
        expect(layout).toContain('<LanguageSwitcher />');
        expect(layout).toContain("<Navigate to={'/manage/events'} />");
    });

    it('keeps password recovery and ticket lookup available', () => {
        expect(login).toContain('/auth/forgot-password');
        expect(login).toContain('Just looking for your tickets?');
        expect(login).toContain('authClient.login(userData)');
    });
});
