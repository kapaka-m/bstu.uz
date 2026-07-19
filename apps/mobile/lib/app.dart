import 'package:flutter/material.dart';

import 'core/config/app_config.dart';
import 'core/localization/app_localizations.dart';
import 'core/network/api_client.dart';
import 'core/routing/app_router.dart';
import 'core/storage/secure_store.dart';
import 'shared/app_state.dart';

class BstuMobileApp extends StatefulWidget {
  const BstuMobileApp({super.key});

  @override
  State<BstuMobileApp> createState() => _BstuMobileAppState();
}

class _BstuMobileAppState extends State<BstuMobileApp> {
  late final AppState state;
  bool ready = false;

  @override
  void initState() {
    super.initState();
    final store = SecureStore();
    final client =
        ApiClient(baseUrl: AppConfig.defaultApiBaseUrl, store: store);
    state = AppState(client: client, store: store);
    state.addListener(_onStateChanged);
    state.bootstrap().whenComplete(() => setState(() => ready = true));
  }

  @override
  void dispose() {
    state.removeListener(_onStateChanged);
    state.dispose();
    super.dispose();
  }

  void _onStateChanged() {
    if (mounted) {
      setState(() {});
    }
  }

  @override
  Widget build(BuildContext context) {
    final locale = Locale(state.locale);
    final textDirection = state.isRtl ? TextDirection.rtl : TextDirection.ltr;
    const primary = Color(0xff0f766e);
    const navy = Color(0xff0f172a);
    const surface = Color(0xfff8fafc);

    return AppScope(
      state: state,
      child: Directionality(
        textDirection: textDirection,
        child: MaterialApp(
          debugShowCheckedModeBanner: false,
          title: 'BSTU International',
          locale: locale,
          supportedLocales: AppLocalizations.supportedLocales,
          localizationsDelegates: AppLocalizations.delegates,
          theme: ThemeData(
            colorScheme: ColorScheme.fromSeed(
              seedColor: primary,
              primary: primary,
              secondary: const Color(0xff2563eb),
              surface: Colors.white,
            ),
            useMaterial3: true,
            scaffoldBackgroundColor: surface,
            fontFamily: 'Roboto',
            appBarTheme: const AppBarTheme(
              centerTitle: false,
              elevation: 0,
              scrolledUnderElevation: 0,
              backgroundColor: surface,
              foregroundColor: navy,
              titleTextStyle: TextStyle(
                color: navy,
                fontSize: 18,
                fontWeight: FontWeight.w800,
              ),
            ),
            navigationBarTheme: NavigationBarThemeData(
              height: 72,
              elevation: 0,
              backgroundColor: Colors.white,
              indicatorColor: primary.withValues(alpha: 0.12),
              labelTextStyle: WidgetStateProperty.all(
                const TextStyle(fontSize: 11, fontWeight: FontWeight.w700),
              ),
            ),
            filledButtonTheme: FilledButtonThemeData(
              style: FilledButton.styleFrom(
                minimumSize: const Size.fromHeight(52),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                textStyle: const TextStyle(fontWeight: FontWeight.w800),
              ),
            ),
            inputDecorationTheme: InputDecorationTheme(
              filled: true,
              fillColor: Colors.white,
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(16),
                borderSide: const BorderSide(color: Color(0xffe5e7eb)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(16),
                borderSide: const BorderSide(color: Color(0xffe5e7eb)),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(16),
                borderSide: const BorderSide(color: primary, width: 1.4),
              ),
            ),
            textTheme: ThemeData.light().textTheme.apply(
                  bodyColor: navy,
                  displayColor: navy,
                ),
            snackBarTheme: SnackBarThemeData(
              behavior: SnackBarBehavior.floating,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
            ),
          ),
          initialRoute: ready ? AppRoutes.home : AppRoutes.splash,
          onGenerateRoute: (settings) =>
              AppRouter.onGenerateRoute(settings, state),
        ),
      ),
    );
  }
}
