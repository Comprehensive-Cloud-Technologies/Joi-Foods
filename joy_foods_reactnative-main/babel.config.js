module.exports = {
  presets: ['module:@react-native/babel-preset'],
  plugins: [
    ['module:react-native-dotenv', {
      envName: 'APP_ENV',
      moduleName: '@env',
      path: '.env',
      blocklist: null,
      allowlist: null,
      safe: true,
      allowUndefined: false,
      verbose: false,
    }],
    'react-native-reanimated/plugin',
    [
      'module-resolver',
      {
        root: ['./src'],
        extensions: ['.tsx', '.ts', '.js', '.jsx', '.json', '.jsonc'],
        alias: { '*': './src/' },
      },
    ],
  ],
};
