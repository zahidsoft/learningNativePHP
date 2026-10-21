import SwiftUI

/// A vertical scroll view that keeps the direct child at `scroll_to` in the
/// middle of the screen whenever that index changes.
///
/// Dragging the list stops the following, so the reader is never pulled away
/// from what they are reading; a new `follow_request` follows again. Each
/// change is reported to PHP through `on_follow_change` as "0" or "1".
struct SpeechFollowScrollRenderer: View {
    let node: NativeUINode

    @State private var isFollowing = true

    var body: some View {
        let target = node.props.getInt("scroll_to", default: -1)
        let followRequest = node.props.getInt("follow_request", default: 0)
        let spacing = CGFloat(node.layout?.gap ?? 0)

        ScrollViewReader { proxy in
            ScrollView(.vertical) {
                LazyVStack(alignment: .leading, spacing: spacing) {
                    ForEach(node.children) { child in
                        NodeView(node: child)
                            .equatable()
                            .frame(maxWidth: .infinity, alignment: .leading)
                            .id(child.id)
                    }
                }
                .frame(maxWidth: .infinity)
            }
            // Only the reader's own drag stops following; scrollTo is not a drag.
            .simultaneousGesture(DragGesture(minimumDistance: 8).onChanged { _ in
                setFollowing(false)
            })
            .onAppear {
                if isFollowing {
                    center(target, with: proxy, animated: false)
                }
            }
            .onChange(of: target) { newTarget in
                if isFollowing {
                    center(newTarget, with: proxy, animated: true)
                }
            }
            .onChange(of: followRequest) { _ in
                setFollowing(true)
                center(target, with: proxy, animated: true)
            }
        }
    }

    private func setFollowing(_ value: Bool) {
        guard isFollowing != value else { return }

        isFollowing = value

        let callback = node.props.getInt("on_follow_change", default: 0)

        if callback != 0 {
            NativeElementBridge.sendTextChangeEvent(callback, nodeId: node.id, text: value ? "1" : "0")
        }
    }

    private func center(_ index: Int, with proxy: ScrollViewProxy, animated: Bool) {
        guard node.children.indices.contains(index) else { return }

        let id = node.children[index].id

        // Deferred past layout: lazy rows are not measured yet on the first pass.
        DispatchQueue.main.async {
            if animated {
                withAnimation(.easeInOut(duration: 0.35)) {
                    proxy.scrollTo(id, anchor: .center)
                }
            } else {
                proxy.scrollTo(id, anchor: .center)
            }
        }
    }
}
