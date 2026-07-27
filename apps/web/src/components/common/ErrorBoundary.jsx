import React from "react";
import ErrorState from "./ErrorState";

export default class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true, error };
  }

  componentDidCatch(error, info) {
    if (import.meta.env.DEV) {
      console.error("Route render failed", error, info);
    }
  }

  reset = () => {
    this.setState({ hasError: false, error: null });
  };

  render() {
    if (this.state.hasError) {
      return (
        <ErrorState
          titleKey="common.errorTitle"
          messageKey="common.pageRenderErrorRetry"
          message={
            import.meta.env.DEV
              ? this.state.error?.message || undefined
              : undefined
          }
          onRetry={this.reset}
          height="h-screen"
        />
      );
    }

    return this.props.children;
  }
}
