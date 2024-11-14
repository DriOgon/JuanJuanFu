<template>
  <div class="app">
    <!-- Header Section -->
    <header class="header">
      <div class="profile">
        <div class="profile-img-placeholder"></div>
        <span class="username">mike</span>
      </div>
      <input type="text" placeholder="输入资料名称" class="search-bar" />
    </header>

    <div class="content">
      <!-- Left Sidebar Section -->
      <aside class="sidebar">
        <ul class="options">
          <li
            v-for="option in options"
            :key="option"
            :class="{ 'active-option': option === selectedOption }"
            class="option-item"
            @click="selectOption(option)"
          >
            {{ option }}
          </li>
        </ul>
      </aside>

      <!-- Main Content Section -->
      <main class="main-content">
        <!-- Categories Section -->
        <section class="categories">
          <div class="category" v-for="category in categories" :key="category.name">
            <div class="category-icon-placeholder"></div>
            <span>{{ category.name }}</span>
          </div>
        </section>

        <!-- Document List Section -->
        <section class="document-list">
          <div
            class="document"
            v-for="doc in filteredDocuments"
            :key="doc.title"
            @click="openDocument(doc.filePath)"
          >
            <div class="document-info">
              <h3 class="document-title">{{ doc.title }}</h3>
              <p class="document-semester">{{ doc.semester }}</p>
              <div class="document-stats">
                <span>{{ doc.views }} views</span>
                <span>{{ doc.downloads }} downloads</span>
              </div>
            </div>
            <div class="document-thumbnail-placeholder"></div>
          </div>
        </section>
      </main>
    </div>

    <!-- Footer Section -->
    <footer class="footer">
      <button class="footer-button" v-for="tab in tabs" :key="tab.name">
        <div class="footer-icon-placeholder"></div>
        <span>{{ tab.name }}</span>
      </button>
    </footer>
  </div>
</template>

<script>
export default {
  data() {
    return {
      selectedOption: '全部',
      options: [
        '全部',
        '大学语文',
        '大学英语',
        '大学物理',
        '力学',
        '概率论',
        '计算机',
        '近代史',
        '马克思主义',
        '毛泽东思想',
      ],
      categories: [
        { name: '教材pdf' },
        { name: 'PPT' },
        { name: 'CET4' },
        { name: 'CET6' },
      ],
      documents: [
        {
          title: '《大学英语》',
          semester: '2022–2023学年第一学期',
          views: '3.1k',
          downloads: '1.9k',
          filePath: '/test.pdf' // 添加文件路径
        },
        {
          title: '《线性代数》',
          semester: '2020–2021学年第一学期',
          views: '5.5k',
          downloads: '3.8k',
     
        },
        {
          title: '《高级语言程序设计》',
          semester: '2023–2024学年秋季学期',
          views: '8.8k',
          downloads: '6.8k',
         
        },
        {
          title: '《计算机组成原理》',
          semester: '2021–2022学年第二学期',
          views: '5k',
          downloads: '1.9k',
         
        },
      ],
      tabs: [
        { name: '社区' },
        { name: '资料库' },
        { name: 'AI' },
        { name: '商城' },
        { name: '我的' },
      ],
    };
  },
  computed: {
    filteredDocuments() {
      if (this.selectedOption === '全部') {
        return this.documents;
      }
      return this.documents.filter(doc =>
        doc.title.includes(this.selectedOption)
      );
    },
  },
  methods: {
    selectOption(option) {
      this.selectedOption = option;
    },
    openDocument(filePath) {
        // 检查文件路径是否为 static/test.pdf
        if (filePath === '/test.pdf') {
          // 使用 window.open 打开 PDF 文件
          window.open(`/static/test.pdf`, '_blank');
        } else {
          // 处理其他本地文件路径
          // 例如，使用 file:// 协议打开本地文件
          window.open(`/static/test.pdf`, '_blank');
        }
      }
  },
};
</script>

<style scoped>
.app {
  font-family: Arial, sans-serif;
  color: #333;
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.header {
  display: flex;
  align-items: center;
  padding: 10px;
}

.profile {
  display: flex;
  align-items: center;
}

.profile-img-placeholder {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #ddd;
  margin-right: 10px;
}

.username {
  font-size: 18px;
  font-weight: bold;
}

.search-bar {
  flex-grow: 1;
  margin-left: 20px;
  padding: 5px;
  border-radius: 20px;
  border: 1px solid #ccc;
}

.content {
  display: flex;
  flex-grow: 1;
  overflow: hidden;
}

.sidebar {
  width: 120px;
  background-color: #f9f9f9;
  padding: 10px;
  overflow-y: auto;
  border-right: 1px solid #eaeaea;
}

.options {
  list-style-type: none;
  padding: 0;
  margin: 0;
}

.option-item {
  padding: 10px;
  cursor: pointer;
  color: #333;
}

.option-item:hover,
.active-option {
  background-color: #ddd;
}

.main-content {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.categories {
  display: flex;
  justify-content: space-around;
  padding: 10px 0;
  background-color: #f9f9f9;
}

.category {
  text-align: center;
}

.category-icon-placeholder {
  width: 40px;
  height: 40px;
  background-color: #ddd;
  border-radius: 50%;
  margin-bottom: 5px;
}

.document-list {
  padding: 10px;
  overflow-y: auto;
}

.document {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  border-bottom: 1px solid #eaeaea;
  cursor: pointer;
}

.document-info {
  max-width: 70%;
}

.document-title {
  font-size: 16px;
  font-weight: bold;
}

.document-semester {
  color: #666;
}

.document-stats {
  color: #999;
  font-size: 12px;
}

.document-thumbnail-placeholder {
  width: 50px;
  height: 70px;
  background-color: #ddd;
}

.footer {
  display: flex;
  justify-content: space-around;
  padding: 10px 0;
  background-color: #f9f9f9;
  border-top: 1px solid #eaeaea;
}

.footer-button {
  display: flex;
  flex-direction: column;
  align-items: center;
  font-size: 12px;
  color: #666;
}

.footer-icon-placeholder {
  width: 24px;
  height: 24px;
  background-color: #ddd;
  margin-bottom: 5px;
}
</style>