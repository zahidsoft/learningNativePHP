package com.nativephp.plugins.speech

import androidx.compose.foundation.gestures.animateScrollBy
import androidx.compose.foundation.interaction.DragInteraction
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyListState
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.lazy.rememberLazyListState
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberUpdatedState
import androidx.compose.runtime.setValue
import androidx.compose.runtime.snapshotFlow
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.nativephp.mobile.ui.nativerender.NativeUIBridge
import com.nativephp.mobile.ui.nativerender.NativeUINode
import com.nativephp.mobile.ui.nativerender.NodeView

/**
 * A vertical scroll view that keeps the direct child at `scroll_to` in the
 * middle of the screen whenever that index changes.
 *
 * Dragging the list stops the following, so the reader is never pulled away
 * from what they are reading; scrolling the child back into view, or a new
 * `follow_request`, follows it again. Each change is reported to PHP through
 * `on_follow_change` as "0" or "1".
 */
object FollowScrollRenderer {
    @Composable
    fun Render(node: NativeUINode, modifier: Modifier) {
        val scrollTo = node.props.getInt("scroll_to", -1)
        val followRequest = node.props.getInt("follow_request", 0)
        val target by rememberUpdatedState(scrollTo)
        val onFollowChange by rememberUpdatedState(node.props.getInt("on_follow_change", 0))
        val nodeId by rememberUpdatedState(node.id)
        val listState = rememberLazyListState()
        var isFollowing by remember { mutableStateOf(true) }

        val setFollowing: (Boolean) -> Unit = { value ->
            if (isFollowing != value) {
                isFollowing = value

                if (onFollowChange != 0) {
                    NativeUIBridge.sendTextChangeEvent(onFollowChange, nodeId, if (value) "1" else "0")
                }
            }
        }

        // Only the reader's own drag stops following; scrolls made here are not drags.
        LaunchedEffect(listState) {
            listState.interactionSource.interactions.collect { interaction ->
                if (interaction is DragInteraction.Start) {
                    setFollowing(false)
                }
            }
        }

        // Once the reader's scroll settles with the recited child in view, follow it again.
        LaunchedEffect(listState) {
            snapshotFlow { listState.isScrollInProgress }.collect { isScrolling ->
                if (!isScrolling && !isFollowing && listState.isShowing(target)) {
                    setFollowing(true)
                }
            }
        }

        LaunchedEffect(scrollTo) {
            if (isFollowing) {
                listState.center(scrollTo)
            } else if (listState.isShowing(scrollTo)) {
                // The recitation reached the part the reader is looking at.
                setFollowing(true)
            }
        }

        LaunchedEffect(followRequest) {
            if (followRequest > 0) {
                setFollowing(true)
                listState.center(target)
            }
        }

        LazyColumn(
            modifier = modifier,
            state = listState,
            verticalArrangement = Arrangement.spacedBy((node.layout?.gap ?: 0f).dp),
        ) {
            items(node.children, key = { it.id }) { child ->
                NodeView(node = child)
            }
        }
    }

    /**
     * Scroll so the item's middle sits in the middle of the screen. An item
     * taller than the screen shows its start instead. Near either end of the
     * list the scroll simply stops at the edge.
     */
    private suspend fun LazyListState.center(index: Int) {
        if (index !in 0 until layoutInfo.totalItemsCount) {
            return
        }

        if (layoutInfo.visibleItemsInfo.none { it.index == index }) {
            animateScrollToItem(index)
        }

        val item = layoutInfo.visibleItemsInfo.firstOrNull { it.index == index } ?: return
        val viewport = layoutInfo.viewportEndOffset - layoutInfo.viewportStartOffset
        val distance = if (item.size >= viewport) item.offset else item.offset + item.size / 2 - viewport / 2

        animateScrollBy(distance.toFloat())
    }

    /**
     * Whether part of the item is in the middle half of the screen.
     */
    private fun LazyListState.isShowing(index: Int): Boolean {
        val viewport = layoutInfo.viewportEndOffset - layoutInfo.viewportStartOffset

        return layoutInfo.visibleItemsInfo.any {
            it.index == index && it.offset < viewport * 3 / 4 && it.offset + it.size > viewport / 4
        }
    }
}
