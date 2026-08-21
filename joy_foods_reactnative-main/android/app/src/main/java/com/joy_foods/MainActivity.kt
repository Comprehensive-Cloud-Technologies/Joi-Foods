package com.joy_foods

import android.os.Bundle
import com.facebook.react.ReactActivity
import com.facebook.react.ReactActivityDelegate
import com.facebook.react.defaults.DefaultNewArchitectureEntryPoint.fabricEnabled
import com.facebook.react.defaults.DefaultReactActivityDelegate

class MainActivity : ReactActivity() {

  override fun onCreate(savedInstanceState: Bundle?) {
    super.onCreate(null)
  }

  /**
   * Returns the name of the main component registered from JavaScript. This is used to schedule
   * rendering of the component.
   */
  override fun getMainComponentName(): String = "joy_foods"

  /**
   * Returns the instance of the [ReactActivityDelegate]. We use [DefaultReactActivityDelegate]
   * which allows you to enable New Architecture with a single boolean flags [fabricEnabled]
   */
  override fun createReactActivityDelegate(): ReactActivityDelegate =
      DefaultReactActivityDelegate(this, mainComponentName, fabricEnabled)

  override fun onResume() {
    super.onResume()
    // Re-apply font scale lock every time app comes to foreground
    // This handles the case where user changed font size in settings and returned to app
    resetFontScale()
  }

  private fun resetFontScale() {
    val config = resources.configuration
    if (config.fontScale != 1.0f) {
      config.fontScale = 1.0f
      @Suppress("DEPRECATION")
      resources.updateConfiguration(config, resources.displayMetrics)
    }
  }
}
