<?php
/**
 * @author 小日日
 * @time 2023/1/24
 */

namespace roc;

class TrieNode
{
    /**
     * @var string 待匹配路由，例如 /p/:lang 相当于完整的URL，但是会根据场景置空
     */
    public string $pattern = '';
    /**
     * @var string 路由中的一部分URL片段
     */
    public string $part = '';

    /**
     * @var array<TrieNode> 子节点，例如 [doc, tutorial, intro]
     */
    public array $children = [];

    /**
     * @var bool 是否精确匹配，part 含有 : 或 * 时为true 和trie树无关，为了http动态路由特别增加的字段
     */
    public bool $isWild;

    /**
     * 入参 height： 节点的层数，顶层root是0，每下一层+1
     * node结构体的patten成员变量，只有当该node已经是最底层的时候，才会赋值为完整的URL，否则为空
     * 查找子节点，若找不到则新建子节点（赋值两个变量：part（当前处理的URL片段）和isWild（是否检测到冒号和星号动态路由标志））
     * 并将子节点放入结构体的子节点成员变量中
     * 递归对子节点做相同（本身函数）的操作
     * @param string $pattern
     * @param array $parts
     * @param int $height
     * @return void
     */
    public function insert(string $pattern, array $parts, int $height)
    {
        if (count($parts) == $height) {
            $this->pattern = $pattern;
            return;
        }
        $part = $parts[$height];
        $child = $this->matchChild($part);
        if ($child === null) {
            $child = new TrieNode();
            $child->part = $part;
            $child->isWild = $part[0] == ":" || $part[0] == '*';
            $this->children[] = $child;
        }
        $child->insert($pattern, $parts, $height + 1);
    }


    public function matchChild(string $part): ?TrieNode
    {
        foreach ($this->children as $child) {
            if ($child->part == $part || $child->isWild) {
                return $child;
            }
        }
        return null;
    }


    /**
     * @param $part
     * @return TrieNode[]
     */
    public function matchChildren($part): array
    {
        $nodes = [new TrieNode()];
        foreach ($this->children as $child) {
            if ($child->part == $part || $child->isWild) {
                $nodes[] = $child;
            }
        }
        return $nodes;
    }

    public function search(array $parts, int $height): ?TrieNode
    {
        // 递归终止条件
        if (count($parts) == $height || (strlen($this->part) > 0 && $this->part[0] == "*")) {
            if ($this->pattern == "") {
                return null;
            }
            return $this;
        }
        $part = $parts[$height];
        $children = $this->matchChildren($part);
        foreach ($children as $child) {
            $result = $child->search($parts, $height + 1);
            if ($result != null) {
                return $result;
            }
        }
        return null;
    }
}