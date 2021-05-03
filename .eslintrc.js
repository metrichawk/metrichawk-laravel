module.exports = {
    root: true,
    env: {
        browser: true
    },
    'extends': [
        'standard',
        'plugin:vue/essential',
        'plugin:vue/recommended',
    ],
    rules: {
        'no-console': 'off',
        'no-debugger': 'off',
        'comma-dangle': ['error', 'always-multiline'],
        semi: [
            'error',
            'always'
        ],
        indent: [
            'error',
            4,
            {
                SwitchCase: 1
            }
        ],
        'vue/no-v-html': 'off',
        'vue/html-indent': [
            'error',
            4,
            {
                attribute: 1,
                closeBracket: 0,
                alignAttributesVertically: true,
                ignores: []
            }
        ],
        'vue/name-property-casing': [
            'error',
            'kebab-case'
        ],
        'vue/attribute-hyphenation': [
            'error',
            'always'
        ],
        'vue/html-end-tags': 'error',
        'vue/require-default-prop': 'error',
        'vue/require-prop-types': 'error',
        'vue/attributes-order': 'error',
        'vue/html-quotes': [
            'error',
            'double'
        ],
        'vue/order-in-components': 'error',
        'vue/html-self-closing': [
            'error', {
                'html': {
                    'void': 'never',
                    'normal': 'never',
                    'component': 'always'
                }
            }]
    },
    parserOptions: {
        parser: 'babel-eslint',
        ecmaVersion: 2017
    }
};
